import express from 'express';
import cors from 'cors';
import bodyParser from "body-parser";
import dotenv from 'dotenv';

import sseRoutes from './routes/sse';
import rot from './routes/router';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Routes
app.use(sseRoutes);
app.use(rot);

app.use(express.static("dist-frontend"));

app.listen(PORT, () => {
    console.log(`Server in ascolto su http://localhost:${PORT}`);
});
