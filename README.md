<div align="center">

<img src="public/favicon-32x32.png" alt="ReelBox Logo" width="80">

# REELBOX

### 🎬 Piattaforma per esplorare Film e Serie TV

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![SQLite](https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://sqlite.org)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)

</div>

---

## 📖 Cos'è ReelBox?

**ReelBox** è una web app sviluppata con **Laravel 12** che permette di esplorare film e serie TV in modo interattivo.  
I contenuti vengono arricchiti automaticamente con **poster**, **trailer** e **trame** tramite API esterne (TMDB, YouTube, Wikipedia).  
Include un **forum** per discutere con altri utenti e un sistema di **preferiti** personalizzato.

---

## ✨ Funzionalità

### 🎬 Esplorazione Contenuti
- Visualizzazione casuale di **film** e **serie TV**
- **Ricerca avanzata** con filtri per genere, anno e tipologia
- Poster automatici via **TMDB API**
- Trailer integrati via **YouTube API**
- Trame recuperate da **Wikipedia API**
- Sfondo dinamico blurrato con il poster del contenuto

### 💬 Forum
- **Categorie** per genere cinematografico con effetto glitch
- Creazione e gestione **post**
- Sistema di **like** sui post
- **Commenti** sui post
- Eliminazione post/commenti per autore e admin

### ❤️ Preferiti & Like
- **Like** sui contenuti dalla home
- Sezione **"I miei preferiti"** nel profilo
- Preferenze generi automatiche basate sui like

### 👤 Profilo Utente
- Visualizzazione **generi preferiti**
- Lista **contenuti salvati**
- Gestione account

### 🛡️ Pannello Admin
- Eliminazione di qualsiasi **post** e **commento**
- Gestione **utenti**

### 🎨 UI/UX Cyberpunk
- Tema **Neon/Cyberpunk** custom
- Font **Orbitron** + **Rajdhani**
- Animazioni **fade-in** e **hover neon**
- Effetto **glitch** sui bottoni del forum
- **Scanline** effect atmosferico

---

## 📸 Screenshots

### 🏠 Home - Esplora Contenuti
![Home](https://raw.githubusercontent.com/GeQ231/Reelbox/main/screenshots/home.png)

### 💬 Forum
![Forum](https://raw.githubusercontent.com/GeQ231/Reelbox/main/screenshots/forum.png)

### 🎬 Pagina Film
![Film](https://raw.githubusercontent.com/GeQ231/Reelbox/main/screenshots/film.png)

### 👤 Profilo
![Profile](https://raw.githubusercontent.com/GeQ231/Reelbox/main/screenshots/profile.png)

---

## 🛠️ Tech Stack

| Categoria | Tecnologia |
|-----------|-----------|
| **Backend** | Laravel 12, PHP 8.4 |
| **Database** | SQLite |
| **Frontend** | Bootstrap 5, CSS Custom Cyberpunk |
| **Font** | Orbitron, Rajdhani (Google Fonts) |
| **Icone** | Bootstrap Icons |
| **API** | TMDB, YouTube Data v3, Wikipedia REST |

---

## 🚀 Installazione

### Prerequisiti
- PHP >= 8.2
- Composer
- SQLite

### 1️⃣ Clona il repository
```bash
git clone https://github.com/GeQ231/Reelbox.git
cd Reelbox
