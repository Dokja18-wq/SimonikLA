const express = require("express");
const mysql = require("mysql2");
const mongoose = require("mongoose");
const http = require("http");
const socketIo = require("socket.io");
const cors = require("cors");
const crypto = require("crypto"); // <-- WAJIB ADA
global.crypto = crypto;
const app = express();
const server = http.createServer(app);
const io = socketIo(server, { cors: { origin: "*" } });

app.use(cors());
app.use(express.json());

// ========== MYSQL ==========
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "db_kinerja_pemerintah", // sesuai dengan database Anda
});
db.connect((err) => {
  if (err) throw err;
  console.log("✅ MySQL connected");
});

// ========== MONGODB ==========
const mongoURI = "mongodb://127.0.0.1:27017/db_kinerja_mongo";
mongoose
  .connect(mongoURI, { family: 4 })
  .then(() => console.log("✅ MongoDB connected"))
  .catch((err) => console.error("❌ MongoDB connection error:", err));

const kinerjaSchema = new mongoose.Schema({
  mysql_id: Number,
  nama_program: String,
  opd: String,
  target: Number,
  realisasi: Number,
  tahun: Number,
  bulan: Number,
  triwulan: Number,
  keterangan: String,
  updated_at: { type: Date, default: Date.now },
});
const KinerjaMongo = mongoose.model("Kinerja", kinerjaSchema);

// ========== LOGIN ==========
app.post("/api/login", (req, res) => {
  const { username, password } = req.body;
  const hashedPassword = crypto
    .createHash("md5")
    .update(password)
    .digest("hex");
  db.query(
    "SELECT * FROM users WHERE username = ? AND password = ?",
    [username, hashedPassword],
    (err, results) => {
      if (err)
        return res
          .status(500)
          .json({ success: false, message: "Server error" });
      if (results.length > 0) {
        res.json({
          success: true,
          message: "Login berhasil",
          role: results[0].role,
        });
      } else {
        res.json({ success: false, message: "Username atau password salah" });
      }
    },
  );
});

// ========== SINKRONISASI DARI PHP ==========
app.post("/api/sync", async (req, res) => {
  try {
    const { action, data } = req.body;
    if (action === "create" || action === "update") {
      await KinerjaMongo.findOneAndUpdate(
        { mysql_id: data.id },
        {
          mysql_id: data.id,
          nama_program: data.nama_program,
          opd: data.opd,
          target: data.target,
          realisasi: data.realisasi,
          tahun: data.tahun,
          bulan: data.bulan,
          triwulan: data.triwulan,
          keterangan: data.keterangan,
          updated_at: new Date(),
        },
        { upsert: true },
      );
    } else if (action === "delete") {
      await KinerjaMongo.deleteOne({ mysql_id: data.id });
    }
    io.emit("data-updated");
    res.json({ status: "ok" });
  } catch (err) {
    console.error("Sync error:", err);
    res.status(500).json({ error: err.message });
  }
});

// ========== DASHBOARD (BACA DARI MONGODB) ==========
app.get("/api/kinerja", async (req, res) => {
  try {
    let filter = {};
    if (req.query.opd) filter.opd = req.query.opd;
    if (req.query.tahun) filter.tahun = parseInt(req.query.tahun);
    if (req.query.bulan) filter.bulan = parseInt(req.query.bulan);
    if (req.query.triwulan) filter.triwulan = parseInt(req.query.triwulan);
    const data = await KinerjaMongo.find(filter).sort({ tahun: -1, bulan: 1 });
    res.json(data);
  } catch (err) {
    console.error(err);
    console.error(err.stack);

    res.status(500).json({
      error: err.message,
      stack: err.stack,
    });
  }
});

app.get("/api/dashboard/summary", async (req, res) => {
  try {
    const match = {};
    if (req.query.tahun) match.tahun = parseInt(req.query.tahun);
    const summary = await KinerjaMongo.aggregate([
      { $match: match },
      {
        $group: {
          _id: "$opd",
          total_target: { $sum: "$target" },
          total_realisasi: { $sum: "$realisasi" },
        },
      },
      {
        $project: {
          opd: "$_id",
          total_target: 1,
          total_realisasi: 1,
          persentase: {
            $cond: [
              { $eq: ["$total_target", 0] },
              0,
              {
                $multiply: [
                  { $divide: ["$total_realisasi", "$total_target"] },
                  100,
                ],
              },
            ],
          },
        },
      },
    ]);
    res.json(summary);
  } catch (err) {
    console.error("Summary error:", err);
    res.status(500).json({ error: err.message });
  }
});

app.post("/api/notify", (req, res) => {
  io.emit("data-updated");
  res.json({ status: "ok" });
});

io.on("connection", (socket) => {
  console.log("Client connected");
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`🚀 Server running on http://localhost:${PORT}`);
});
