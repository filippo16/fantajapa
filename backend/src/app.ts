import express from 'express';
import cors from 'cors';
import bodyParser from "body-parser";
import dotenv from 'dotenv';

import https from 'https';
import fs from 'fs';

import sseRoutes from './routes/sse';
import rot from './routes/router';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 80;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Routes
app.use(sseRoutes);
app.use(rot);

app.use(express.static("dist-frontend"));

const httpsOptions = {
    key: fs.readFileSync('path/to/your/private.key'),
    cert: fs.readFileSync('path/to/your/certificate.crt')
  };
  
  // Start HTTPS server
  https.createServer(httpsOptions, app).listen(PORT, () => {
    console.log(`Server in ascolto su https://localhost:${PORT}`);
  });
