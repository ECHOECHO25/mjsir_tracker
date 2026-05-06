# MJSIR Tracker - Editorial Workflow Platform

MJSIR Tracker is a comprehensive manuscript management and editorial workflow platform designed for academic and scientific journals. It streamlines the entire publication process from initial prescreening to final publication decisions.

## 🚀 Tech Stack

- **Frontend:** Vue 3, Vite, JavaScript, Vanilla CSS
- **Backend:** CodeIgniter 4 (PHP 8+)
- **Database:** MySQL
- **API Communication:** Axios

---

## 👥 User Roles & Permissions

The system is designed around three distinct editorial roles, each with specific permissions and views:

### 1. REPO Staff
- Responsible for the initial intake and prescreening of manuscripts.
- Uploads Turnitin reports and original manuscript files.
- Creates tasks and assigns them to the workflow.
- Manages final "Accepted" or "Rejected" decisions in the Article Status tracker.
- Manages System Users.

### 2. Editor-in-Chief (EIC)
- Overviews the entire queue of manuscripts.
- Provides initial instructions and assigns manuscripts to Associate Editors.
- Reviews recommendations from Associate Editors and makes the final decision on whether an article is rejected, returned for revision, or accepted.

### 3. Associate Editor (AE)
- Receives assigned manuscripts from the EIC.
- Manages the peer-review process (tracks 1st, 2nd, and 3rd review rounds).
- Uploads reviewed/marked-up documents and provides remarks/recommendations back to the EIC.
- Manages suggested peer reviewers.

---

## ⚙️ Core Features

- **Multi-Round Review Tracking:** Supports up to 3 rounds of peer review, independently tracking revised documents and external drive links for each round.
- **Action Tracking:** Dedicated fields for tracking "Action Taken" documents (e.g., rejection letters, revision instructions).
- **Article Status Dashboard:** A centralized tracking board for active/pending decisions, accepted publications, and rejected submissions.
- **Real-Time Notifications:** In-app notification system alerting users when a manuscript is assigned to them or its status changes.
- **User Management:** Built-in UI to add, edit, and remove REPO Staff and Associate Editors securely.

---

## 🛠️ Installation & Setup Guide

Follow these steps to set up the MJSIR Tracker on a new device or server.

### Prerequisites
- PHP 8.0 or newer
- Composer (for PHP dependencies)
- Node.js & npm (for Frontend)
- MySQL Server (e.g., via XAMPP/WAMP)

### 1. Database Setup
1. Open phpMyAdmin or your MySQL client.
2. Create a new, empty database named `mjsir_db`.

### 2. Backend Setup
1. Open a terminal and navigate to the `backend` folder.
2. Copy the `env` file to `.env` and configure your database credentials:
   ```env
   database.default.hostname = localhost
   database.default.database = mjsir_db
   database.default.username = root
   database.default.password = 
   ```
3. Run the database migration to instantly import the schema and default users:
   ```bash
   php spark migrate
   ```
4. Start the backend server:
   ```bash
   php spark serve
   ```
   *(The API will run on `http://localhost:8080` by default).*

### 3. Frontend Setup
1. Open a new terminal and navigate to the `frontend` folder.
2. Install the required Node dependencies:
   ```bash
   npm install
   ```
3. Ensure your `frontend/.env` file (if any) points to the correct backend API URL.
4. Start the development server:
   ```bash
   npm run dev
   ```
5. Open your browser and navigate to the local Vite URL (usually `http://localhost:5173`).

---

## 📂 Project Structure

```text
mjsir_tracker/
├── backend/                   # CodeIgniter 4 API Backend
│   ├── app/
│   │   ├── Controllers/Api/   # RESTful API Endpoints (Manuscripts, Auth, Users)
│   │   ├── Models/            # Database Models
│   │   └── Database/          # Migrations (Auto-imports mjsir_db_backup.sql)
│   └── public/uploads/        # Storage for manuscript and Turnitin files
│
└── frontend/                  # Vue 3 Frontend
    ├── src/
    │   ├── views/             # Main dashboard screens (MyTasks, ArticleStatus, etc.)
    │   ├── components/        # Reusable UI components
    │   ├── router/            # Vue Router configuration
    │   └── assets/            # CSS styles and images (Logos)
    └── index.html             # Main HTML entry point
```
