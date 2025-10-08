// Questo codice DEVE essere eseguito DOPO che React e ReactDOM sono stati caricati, 
// e DOPO che l'elemento 'chat-root-container' è nel DOM.

import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './ChatApp'; // Questo deve puntare al tuo modulo ChatApp compilato

const container = document.getElementById('chat-root-container');

if (container) {
    const root = createRoot(container);
    // Nota: la classe 'h-screen' all'interno di ChatApp.jsx è stata sostituita
    // dalla height fissa di 600px per funzionare bene in un contenitore limitato.
    root.render(<App />);
}
