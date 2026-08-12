import express from "express";
import { spawn } from "child_process";
import http from "http";

const app = express();
const PORT = 3000;
const PHP_PORT = 8000;

// Spawn PHP built-in web server
const phpProcess = spawn("php", ["-S", `127.0.0.1:${PHP_PORT}`, "router.php"], {
  stdio: "inherit",
  cwd: process.cwd()
});

phpProcess.on("error", (err) => {
  console.error("Failed to start PHP server:", err);
});

// Proxy all HTTP requests to PHP built-in server
app.use((req, res) => {
  const options: http.RequestOptions = {
    hostname: "127.0.0.1",
    port: PHP_PORT,
    path: req.url,
    method: req.method,
    headers: {
      ...req.headers,
      host: `127.0.0.1:${PHP_PORT}`
    }
  };

  const proxyReq = http.request(options, (proxyRes) => {
    res.writeHead(proxyRes.statusCode || 500, proxyRes.headers);
    proxyRes.pipe(res, { end: true });
  });

  proxyReq.on("error", (err) => {
    console.error("Proxy connection error:", err.message);
    if (!res.headersSent) {
      res.status(502).send("NEXORA PHP server is starting up, please refresh in a moment...");
    }
  });

  req.pipe(proxyReq, { end: true });
});

app.listen(PORT, "0.0.0.0", () => {
  console.log(`NEXORA Server running on http://0.0.0.0:${PORT}`);
});
