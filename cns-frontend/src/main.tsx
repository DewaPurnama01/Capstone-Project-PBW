import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.tsx';
import './index.css';

/**
 * Titik masuk paling awal aplikasi React. index.html punya satu <div id="root">
 * kosong — baris di bawah ini yang "menyuntikkan" seluruh aplikasi (<App />)
 * ke dalam div tsb. Ini konsep SPA (Single Page Application): satu file HTML,
 * isinya digambar sepenuhnya oleh JavaScript.
 */
createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
