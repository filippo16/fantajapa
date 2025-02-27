import express from 'express';
import cors from 'cors';
import dotenv from 'dotenv';

import sseRoutes from './routes/sse';
import rot from './routes/router';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
// Routes
app.use(sseRoutes);
app.use(rot);


app.listen(PORT, () => {
    console.log(`Server in ascolto su http://localhost:${PORT}`);
});
