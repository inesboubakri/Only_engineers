<?php
require_once '../model/get_hackathons.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier et créer le dossier des images si nécessaire
$imageDir = dirname(__DIR__) . '/ressources/hackathon_images/';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// Log pour le débogage
error_log("Dossier images: " . $imageDir);
error_log("Image par défaut existe: " . (file_exists(__DIR__ . '/../ressources/cybersecurity.png') ? 'Oui' : 'Non'));

// Fetch all hackathons from the database
$hackathons = getAllHackathons();
$hackathonCount = count($hackathons);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WerkLinker - Hackathons</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Ne pas charger Leaflet ici, il sera chargé dynamiquement quand nécessaire -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap');
            .chatbot-popup {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 400px;
                height: 600px;
                background-color: #fff;
                overflow: hidden;
                border-radius: 15px;
                box-shadow: 0 0 128px 0 rgba(0, 0, 0, 0.1),
                            0 32px 64px -48px rgba(0, 0, 0, 0.5);
                display: none; /* Masquer le chatbot initialement */
                z-index: 1000; /* Assurez-vous que le chatbot est au-dessus des autres éléments */
            }
    
            .chatbot-popup.visible {
                display: block; /* Afficher le chatbot lorsque la classe 'visible' est ajoutée */
            }
            
            .chat-header {
                display: flex;
                align-items: center;
                background: var(--primary-purple);
                padding: 15px 22px;
                justify-content: space-between;
                background-color: #6366f1;
    
            }
            
            .header-info {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .bot-avatar {
                height: 35px;
                width: 35px;
                padding: 6px;
                fill: var(--white);
                flex-shrink: 0;
                background: var(--primary-dark);
                border-radius: 50%;
                color: #fff;
            }
            
            .logo-texte {
                color:#fff;
                font-size: 1.31rem;
                font-weight: 600;
            }
            
            .chat-body {
                padding: 25px 22px;
                display: flex;
                flex-direction: column;
                gap: 20px;
                height: 400px; /* Hauteur fixe pour permettre le défilement */
                overflow-y: auto; /* Activer le défilement vertical */
                margin-bottom: 82px;
                background-color: #fff;
                scrollbar-width: auto; /* "auto" ou "thin" */
                scrollbar-color: #888 #f1f1f1; /* Couleur du thumb et de la track */
            }
            .chat-body::-webkit-scrollbar {
                width: 12px; /* Largeur de la scrollbar */
            }
            
            .chat-body::-webkit-scrollbar-track {
                background: #f1f1f1; /* Couleur de la track (arrière-plan) */
                border-radius: 10px;
            }
            
            .chat-body::-webkit-scrollbar-thumb {
                background-color: #888; /* Couleur du thumb (la partie déplaçable) */
                border-radius: 10px;
                border: 3px solid #f1f1f1; /* Bordure pour donner un effet "rembourré" */
            }
            
            /* Styles pour les messages */
            .chat-body .message {
                display: flex;
                gap: 11px;
                align-items: center;
                opacity: 0;
                animation: fadeIn 0.5s forwards;
            }
            
            .chat-body .user-message {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
            }
            
            .chat-body .message-text {
                padding: 12px 16px;
                max-width: 75%;
                font-size: 0.95rem;
                border-radius: 10px;
                
            }
            
            .chat-body .bot-message .message-text {
                background: #F2F2FF;
                border-radius: 13px 13px 13px 3px;
                
            }
            
            .chat-body .user-message .message-text {
                color: #fff;
                background: #5350C4;
                border-radius: 13px 3px 13px 13px;
            }
            
            /* Styles pour la barre de défilement */
            .chat-body::-webkit-scrollbar {
                width: 8px;
            }
            
            .chat-body::-webkit-scrollbar-thumb {
                background: #5350C4;
                border-radius: 10px;
            }
            
            .chat-body::-webkit-scrollbar-track {
                background: #F2F2FF;
            }
            
            .thinking-indicator {
                display: flex;
                gap: 4px;
                padding-block: 15px;
                justify-content: flex-start;
            }
            
            .thinking-indicator .dot {
                height: 7px;
                width: 7px;
                border-radius: 50%;
                background: var(--primary-purple);
                animation: dotPulse 1.8s ease-in-out infinite;
            }
            
            @keyframes dotPulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.5);
                }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .chat-footer {
                position: absolute;
                bottom: 0;
                width: 100%;
                background: var(--white);
                padding: 10px;
                box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
                
            }
            
            .chat-footer .chat-form {
                display: flex;
                gap: 10px;
                align-items: center;
                background-color: #fff;
            }
            
            .message-input {
                flex-grow: 1;
                padding: 10px;
                border: 1px solid var(--text-gray);
                border-radius: 8px;
                font-size: 0.95rem;
                background: var (--bg-light);
                color: var(--text-dark);
            }
            
            .chat-controls button {
                border: none;
                background: none;
                color: var(--primary-purple);
                font-size: 1.5rem;
                cursor: pointer;
            }
            
            .chat-controls button:hover {
                color: var(--primary-dark);
            }
            
            /* Scrollbar Styling */
            .chat-body::-webkit-scrollbar {
                width: 8px;
            }
            
            .chat-body::-webkit-scrollbar-thumb {
                background: var(--primary-purple);
                border-radius: 10px;
            }
            
            .chat-body::-webkit-scrollbar-track {
                background: var(--bg-light);
            }
            
            .message-input {
                flex-grow: 1;
                padding: 12px 16px; /* Augmenter le padding pour un meilleur confort */
                border: 2px solid var(--primary-purple); /* Bordure plus épaisse et colorée */
                border-radius: 12px; /* Bordures plus arrondies */
                font-size: 0.95rem;
                background: var(--white); /* Fond blanc */
                color: var (--text-dark);
                resize: none; /* Empêcher le redimensionnement */
                outline: none; /* Supprimer la bordure de focus par défaut */
                transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Animation fluide */
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombre légère */
                width: 50%; /* Largeur fixe */
                height: 50px; /* Hauteur fixe */
                
            }
            
            .message-input:focus {
                border-color: var(--primary-dark); /* Changement de couleur au focus */
                box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2); /* Ombre plus prononcée au focus */
            }
            
            .message-input::placeholder {
                color: var(--text-gray); /* Couleur du placeholder */
                opacity: 0.7; /* Légère transparence */
            }
            .chatbot-button{
                background-color: white;
    padding: 0.375rem 1.25rem;
    border-radius: 100px;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgba(0, 0, 0, 0.7);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-color: transparent;
        }

        /* User Cards - Matching the networking.php styling */
        .job-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            width: 100%;
            transition: all 0.3s ease;
            grid-auto-flow: dense; /* Permet au grid de remplir les espaces vides */
        }

        .hackathon-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .promo-card {
            grid-column: 2; /* Fixe à la deuxième colonne */
            grid-row: 1; /* Fixe à la première ligne */
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            background-image: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .promo-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .promo-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .promo-content .learn-more {
            background: white;
            color: #6366f1;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .promo-content .learn-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .hackathon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .hackathon-content {
            background-color: #ddd7ff;
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .hackathon-content.amazon { background-color: #ddd7ff; }
        .hackathon-content.google { background-color: #ddd7ff; }
        .hackathon-content.dribbble { background-color: #ddd7ff; }
        .hackathon-content.airbnb { background-color: #ddd7ff; }
        .hackathon-content.mlh { background-color: #ddd7ff; }
        .hackathon-content.microsoft { background-color: #ddd7ff; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .date {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.6);
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 0.875rem;
            border-radius: 20px;
        }

        .bookmark {
            background: rgba(0, 0, 0, 0.05);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(0, 0, 0, 0.6);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .bookmark:hover {
            background: rgba(0, 0, 0, 0.1);
            color: rgba(0, 0, 0, 0.8);
        }

        .bookmark.active {
            color: #FF3B30;
        }

        .bookmark.active svg {
            fill: #FF3B30;
        }

        .hackathon-name-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .hackathon-name-title .title-container {
            flex: 1;
        }

        .organizer-name {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 0.25rem;
            display: block;
        }

        .hackathon-name-title h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.9);
            line-height: 1.2;
            margin: 0;
        }

        .hackathon-image {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
        }

        .hackathon-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto;
        }

        .hackathon-tags span {
            background: rgba(255, 255, 255, 0.7);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.7);
            font-weight: 450;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            margin-top: auto;
        }

        .hackathon-location {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.5);
        }

        .details {
            background: #000000;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .details:hover {
            background: #333333;
            transform: translateY(-1px);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .job-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .job-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode adjustments */
        :root[data-theme="dark"] .hackathon-card {
            background-color: var(--bg-card);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        :root[data-theme="dark"] .hackathon-content {
            background-color: var(--bg-card);
        }

        :root[data-theme="dark"] .hackathon-tags span {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        :root[data-theme="dark"] .details {
            background: #4F6EF7;
            color: white;
        }

        :root[data-theme="dark"] .details:hover {
            background: #3D5CE5;
        }
        
        /* Modal styling for hackathon request form */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            cursor: pointer;
            color: #718096;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            padding: 15px 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            border-color: #4c6ef5;
            outline: none;
        }
        
        .help-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        
        .error-message {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
        }
        
        /* Button styling */
        .cancel-btn,
        .save-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .cancel-btn {
            background-color: #e2e8f0;
            color: #4a5568;
            margin-right: 10px;
        }
        
        .save-btn {
            background-color: #4c6ef5;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #cbd5e0;
        }
        
        .save-btn:hover {
            background-color: #3b5bdb;
        }
        
        .save-btn:disabled {
            background-color: #a0aec0;
            cursor: not-allowed;
        }
        
        /* Image preview */
        .image-preview {
            margin-top: 10px;
            display: none;
            max-width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
        }

        /* Ajout d'un style pour l'indicateur de chargement de la carte */
        .map-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-left">
                <a href="#" class="logo">
                    <img src="../assets/logo.png" alt="Only Engineers">
                </a>
            </div>
            <div class="nav-center">
                <nav class="nav-links">
                    <a href="home.html">Home</a>
                    <a href="../view/Dashboard.html"> Dashboard </a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.html">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathons.php" class="active">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="networking.php">Networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="notification-wrapper">
                    <button class="icon-button notification">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                    <div class="notification-dot"></div>
                </div>
                <button class="icon-button theme-toggle" id="themeToggle">
                    <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <div class="user-profile">
                    <a href="../view/user-profile.php" 
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="filters">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button class="clear-all">Clear all</button>
                    </div>
                    <div class="search-container">
                        <div class="search-bar">
                            <input type="text" value="...">
                        </div>
                        
                    </div>
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Type</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="type">
                                <span class="label-text">Online</span>
                                <span class="count">24</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="type">
                                <span class="label-text">In-Person</span>
                                <span class="count">16</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="type">
                                <span class="label-text">Hybrid</span>
                                <span class="count">8</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Prize Pool</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="prize">
                                <span class="label-text">$1K - $5K</span>
                                <span class="count">12</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="prize">
                                <span class="label-text">$5K - $10K</span>
                                <span class="count">8</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="prize">
                                <span class="label-text">$10K+</span>
                                <span class="count">4</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Duration</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="duration">
                                <span class="label-text">24 Hours</span>
                                <span class="count">15</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="duration">
                                <span class="label-text">48 Hours</span>
                                <span class="count">10</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="duration">
                                <span class="label-text">1 Week</span>
                                <span class="count">5</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Hackathons Section -->
            <section class="jobs">
                <div class="jobs-header">
                    <h2>Hackathons <span class="count"><?php echo $hackathonCount; ?></span></h2>
                    <button class="chatbot-button">
                        🤖 chatbot
                    </button>
                    <div class="chatbot-popup" id="chatbot-popup">
                        <div class="chat-header">
                            <div class="header-info">
                                <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 1024 1024">
                                    <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4 .7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
                                </svg>
                                <h2 class="logo-texte">OnlyEngineersBot</h2>
                            </div>
                        </div>
                
                        <div class="chat-body">
                            <div class="message bot-message">
                                <div class="message-text">Hey there 🔴 <br /> How can I help you today?</div>
                            </div>
                            <!-- Indicateur de chargement -->
                            <div class="thinking-indicator" style="display: none;">
                                <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 1024 1024">
                                    <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4 .7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
                                </svg>
                                <div class="dots">
                                    <span>...</span>
                                </div>
                            </div>
                        </div>
                
                        <!-- Chatbot Footer -->
                        <div class="chat-footer">
                            <form action="#" class="chat-form">
                                <textarea placeholder="Message..." class="message-input" required></textarea>
                                <div class="chat-controls">
                                    <button type="submit" id="send-message" class="material-symbols-rounded">⬆️</button>
                                </div>
                            </form>
                        </div>
                    </div>
                
                <script>
                    document.querySelector('.chatbot-button').addEventListener('click', function() {
                        const chatbotPopup = document.getElementById('chatbot-popup');
                        chatbotPopup.classList.toggle('visible');
                    });
                </script>
                
                <script>
                    const chatBody = document.querySelector(".chat-body");
                    const messageInput = document.querySelector(".message-input");
                    const thinkingIndicator = document.querySelector(".thinking-indicator");
                
                    const API_KEY = "AIzaSyB4Jhhap6wRdbZJupLVA23G5Nho_C6TWmM"; // Remplacez par votre clé API
                    const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${API_KEY}`;
                
                    const generateBotResponse = async (message) => {
                        const requestOptions = {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({
                                contents: [{
                                    parts: [{ text: message }]
                                }]
                            })
                        };
                
                        try {
                            // Fetch bot response from API
                            const response = await fetch(API_URL, requestOptions);
                            const data = await response.json();
                            if (!response.ok) throw new Error(data.error.message);
                
                            console.log("Réponse de l'API:", data); // Afficher la réponse dans la console
                
                            // Extraire la réponse du bot
                            const botResponse = data.candidates[0].content.parts[0].text;
                            return botResponse;
                        } catch (error) {
                            console.error("Erreur lors de la génération de la réponse du bot:", error);
                            return "Désolé, une erreur s'est produite.";
                        }
                    };
                
                    // Create message element with dynamic classes and return it
                    const createMessageElement = (content, classes) => {
                        const div = document.createElement("div");
                        div.classList.add("message", ...classes.split(" "));
                        div.innerHTML = `<div class="message-text">${content}</div>`;
                        return div;
                    };
                
                    // Create thinking indicator element
                    const createThinkingIndicator = () => {
                        const thinkingDiv = document.createElement("div");
                        thinkingDiv.classList.add("message", "bot-message");
                        thinkingDiv.innerHTML = `
                            <div class="message-text">
                                <div class="thinking-indicator">
                                    <div class="dot"></div>
                                    <div class="dot"></div>
                                    <div class="dot"></div>
                                </div>
                            </div>
                        `;
                        return thinkingDiv;
                    };
                
                    const handleOutgoingMessage = async (message) => {
                        if (!message.trim()) return; // Ignore empty messages
                
                        // Create and display user message
                        const outgoingMessageDiv = createMessageElement(message, "user-message");
                        chatBody.appendChild(outgoingMessageDiv);
                        chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
                
                        // Show thinking indicator
                        const thinkingDiv = createThinkingIndicator();
                        chatBody.appendChild(thinkingDiv);
                        chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
                
                        // Generate bot response
                        const botResponse = await generateBotResponse(message);
                        console.log("Réponse du bot:", botResponse); // Afficher la réponse du bot dans la console
                
                        // Simulate a bot response after 1 second
                        setTimeout(() => {
                            // Remove thinking indicator
                            thinkingDiv.remove();
                
                            // Create and display bot message
                            const messageContent = `
                                <div>
                                    <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
                                        <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4 .7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
                                    </svg>
                                    <div class="message-text">${botResponse}</div>
                                </div>
                            `;
                
                            const incomingMessageDiv = createMessageElement(messageContent, "bot-message");
                            chatBody.appendChild(incomingMessageDiv);
                            chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
                        }, 600);
                    };
                
                    // Handle Enter key press for sending messages
                    messageInput.addEventListener("keydown", (e) => {
                        if (e.key === "Enter" && e.target.value.trim()) {
                            e.preventDefault();
                            handleOutgoingMessage(e.target.value.trim());
                            e.target.value = ""; // Clear the input field
                        }
                    });
                
                    // Handle form submission
                    document.querySelector(".chat-form").addEventListener("submit", (e) => {
                        e.preventDefault();
                        const message = messageInput.value.trim();
                        if (message) {
                            handleOutgoingMessage(message);
                            messageInput.value = ""; // Clear the input field
                        }
                    });
                </script>
                    <div class="sort">
                        <span>Sort by:</span>
                        <select>
                            <option>Last updated</option>
                        </select>
                        <button class="view-toggle">
                            <svg class="grid-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <svg class="list-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="job-cards">
                    <!-- Promo card toujours affichée à la première ligne, deuxième colonne -->
                    <div class="promo-card">
                        <div class="promo-content">
                            <h2>Reach thousands of engineers</h2>
                            <button class="learn-more">Add a new hackathon</button>
                        </div>
                    </div>

                    <?php if (empty($hackathons)): ?>
                        <div class="empty-state">
                            <p>No hackathons available at the moment.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        // Define background content classes to cycle through
                        $bgClasses = ['amazon', 'google', 'dribbble', 'airbnb', 'mlh', 'microsoft'];
                        $bgCount = count($bgClasses);
                        
                        foreach ($hackathons as $index => $hackathon): 
                            // Use modulo to cycle through background classes
                            $bgClass = $bgClasses[$index % $bgCount];
                            
                            // Format dates for display
                            $startDate = new DateTime($hackathon['start_date']);
                            $endDate = new DateTime($hackathon['end_date']);
                            $dateDisplay = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
                            
                            // Fix the image path construction
                            $imageFilename = !empty($hackathon['image']) ? $hackathon['image'] : 'default_hackathon.jpg';
                            // Remove any potential duplicate 'ressources/' in the path
                            $imageFilename = str_replace('ressources/', '', $imageFilename);
                            $imagePath = '../ressources/' . $imageFilename;
                            $defaultImage = '../ressources/cybersecurity.png';
                            
                            // Vérifier l'existence de l'image
                            $fullImagePath = __DIR__ . '/../ressources/' . $imageFilename;
                            
                            if (!file_exists($fullImagePath)) {
                                $imagePath = $defaultImage;
                            }
                        ?>
                        <div class="hackathon-card" data-id="<?php echo $hackathon['id']; ?>">
                            <div class="hackathon-content <?php echo $bgClass; ?>">
                                <div class="card-header">
                                    <span class="date"><?php echo $dateDisplay; ?></span>
                                    <button class="bookmark" data-hackathon-id="<?php echo $hackathon['id']; ?>">
                                        <svg class="bookmark-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="hackathon-name-title">
                                    <div class="title-container">
                                        <span class="organizer-name"><?php echo htmlspecialchars($hackathon['organizer']); ?></span>
                                        <h4><?php echo htmlspecialchars($hackathon['name']); ?></h4>
                                    </div>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="<?php echo htmlspecialchars($hackathon['name']); ?>" 
                                         class="hackathon-image"
                                         onerror="this.src='<?php echo htmlspecialchars($defaultImage); ?>'">
                                </div>
                                
                                <div class="hackathon-tags">
                                    <?php if(!empty($hackathon['location'])): ?>
                                    <span><?php echo htmlspecialchars($hackathon['location']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    if(!empty($hackathon['required_skills'])): 
                                        $skills = explode(',', $hackathon['required_skills']);
                                        $displaySkills = array_slice($skills, 0, 2); // Display max 2 skills
                                        foreach ($displaySkills as $skill) {
                                            echo '<span>' . htmlspecialchars(trim($skill)) . '</span>';
                                        }
                                    endif; 
                                    ?>
                                    
                                    <?php if(!empty($hackathon['max_participants'])): ?>
                                    <span><?php echo htmlspecialchars($hackathon['participant_count'] ?? 0); ?>/<?php echo htmlspecialchars($hackathon['max_participants']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div>
                                    <div class="hackathon-location"><?php echo htmlspecialchars($hackathon['theme'] ?? 'Hackathon'); ?></div>
                                </div>
                                <a href="hackathon-details.php?id=<?php echo $hackathon['id']; ?>" class="details">Details</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Hackathon Details Modal -->
    <div class="job-details-modal" id="hackathonDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left">
                    <button class="close-modal">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="header-right">
                    <button class="bookmark-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 1 1 2 2z"/>
                        </svg>
                    </button>
                    <button class="share-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                            <polyline points="16 6 12 2 8 6"/>
                            <line x1="12" y1="2" x2="12" y2="15"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="modal-body">
                <div class="job-header">
                    <h1 class="job-title"></h1>
                    <div class="company-info">
                        <img class="company-logo" src="" alt="Company logo">
                        <div class="company-details">
                            <h2 class="company-name"></h2>
                            <p class="company-location"></p>
                        </div>
                    </div>
                    <div class="job-meta"></div>
                </div>

                <div class="job-content">
                    <div class="section">
                        <h3>About this hackathon</h3>
                        <p class="job-description"></p>
                    </div>

                    <div class="section">
                        <h3>Requirements</h3>
                        <ul class="qualifications-list"></ul>
                    </div>

                    <div class="section">
                        <h3>Prizes</h3>
                        <ul class="responsibilities-list"></ul>
                    </div>

                    <div class="section">
                        <h3>Similar Hackathons</h3>
                        <div class="similar-jobs"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="delete-front-btn">Delete</button>
                <button class="edit-front-btn">Edit</button>
                
                <button class="apply-now-btn">Register Now</button>
            </div>
        </div>
    </div>

    <!-- Hackathon Request Modal -->
    <div class="modal" id="hackathonRequestModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Submit Hackathon Request</h2>
                <button class="close-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <form id="hackathon-request-form" action="../model/submit_hackathon_request.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="hackathon-name">Hackathon Name*</label>
                        <input type="text" id="hackathon-name" name="name" required>
                        <div class="error-message" id="name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon-description">Description*</label>
                        <textarea id="hackathon-description" name="description" rows="4" required></textarea>
                        <div class="error-message" id="description-error"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start-date">Start Date*</label>
                            <input type="date" id="start-date" name="start_date" required>
                            <div class="error-message" id="start-date-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end-date">End Date*</label>
                            <input type="date" id="end-date" name="end_date" required>
                            <div class="error-message" id="end-date-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start-time">Start Time*</label>
                            <input type="time" id="start-time" name="start_time" required>
                            <div class="error-message" id="start-time-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end-time">End Time*</label>
                            <input type="time" id="end-time" name="end_time" required>
                            <div class="error-message" id="end-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location*</label>
                        <input type="text" id="location" name="location" required>
                        <div class="error-message" id="location-error"></div>
                        
                        <!-- Map container for selecting location -->
                        <div id="location-map" style="height: 300px; margin-top: 10px; border-radius: 8px;"></div>
                        
                        <!-- Hidden fields for storing latitude and longitude -->
                        <input type="hidden" id="latitude" name="latitude" value="">
                        <input type="hidden" id="longitude" name="longitude" value="">
                        
                        <div class="help-text">Click on the map to select the exact location for your hackathon.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="required-skills">Required Skills*</label>
                        <textarea id="required-skills" name="required_skills" rows="3" required></textarea>
                        <div class="help-text">Enter skills separated by commas (e.g., Python, Machine Learning, Web Development)</div>
                        <div class="error-message" id="skills-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="organizer">Organizer*</label>
                        <input type="text" id="organizer" name="organizer" required>
                        <div class="error-message" id="organizer-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="max-participants">Maximum Participants*</label>
                        <input type="number" id="max-participants" name="max_participants" min="1" required>
                        <div class="error-message" id="max-participants-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon-image">Hackathon Image*</label>
                        <input type="file" id="hackathon-image" name="image" accept="image/*" required>
                        <div class="help-text">Upload an image for the hackathon (max size: 2MB)</div>
                        <div class="error-message" id="image-error"></div>
                        <div class="image-preview" id="image-preview"></div>
                    </div>
                    <input type="hidden" name="status" value="pending">
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="cancel-btn" id="cancel-hackathon-request">Cancel</button>
                <button class="save-btn" id="submit-hackathon-request">Submit Request</button>
            </div>
        </div>
    </div>

    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Wait until the modal is opened to initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            const addHackathonBtn = document.querySelector('.promo-card .learn-more');
            const hackathonRequestModal = document.getElementById('hackathonRequestModal');
            const closeModalBtn = document.querySelector('#hackathonRequestModal .close-modal');
            const cancelBtn = document.getElementById('cancel-hackathon-request');
            let mapInitialized = false;
            
            // Fonction pour initialiser la carte
            function initMap() {
                console.log("Début d'initialisation de la carte...");
                
                const mapContainer = document.getElementById('location-map');
                if (!mapContainer) {
                    console.error("Conteneur de carte introuvable");
                    return;
                }
                
                // Vérifier si la div de la carte a une hauteur
                const containerHeight = mapContainer.clientHeight;
                console.log("Hauteur du conteneur de carte:", containerHeight);
                if (containerHeight === 0) {
                    console.warn("Le conteneur de carte a une hauteur de 0, ajustement...");
                    mapContainer.style.height = '300px';
                }
                
                // Afficher un indicateur de chargement
                const loader = document.createElement('div');
                loader.className = 'map-loading';
                loader.textContent = 'Chargement de la carte...';
                mapContainer.appendChild(loader);
                
                // Charger Leaflet si nécessaire
                if (typeof L === 'undefined') {
                    console.log("Leaflet n'est pas chargé, chargement des scripts...");
                    
                    // Charger la CSS de Leaflet
                    if (!document.querySelector('link[href*="leaflet.css"]')) {
                        const leafletCSS = document.createElement('link');
                        leafletCSS.rel = 'stylesheet';
                        leafletCSS.href = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css';
                        document.head.appendChild(leafletCSS);
                    }
                    
                    // Charger le script Leaflet
                    const leafletScript = document.createElement('script');
                    leafletScript.src = 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js';
                    
                    leafletScript.onload = function() {
                        console.log("Leaflet chargé avec succès");
                        createMap(mapContainer, loader);
                    };
                    
                    leafletScript.onerror = function() {
                        console.error("Erreur lors du chargement de Leaflet");
                        loader.textContent = "Erreur de chargement de la carte";
                        loader.style.color = 'red';
                    };
                    
                    document.head.appendChild(leafletScript);
                } else {
                    console.log("Leaflet déjà chargé");
                    createMap(mapContainer, loader);
                }
            }
            
            // Fonction pour créer la carte une fois Leaflet chargé
            function createMap(container, loader) {
                try {
                    console.log("Création de la carte...");
                    
                    // Initialiser la carte avec des coordonnées par défaut (Paris)
                    const map = L.map('location-map').setView([48.8566, 2.3522], 13);
                    
                    // Ajouter les tuiles OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    
                    // Supprimer le loader
                    loader.remove();
                    
                    // Forcer un rafraîchissement de la carte
                    setTimeout(() => {
                        console.log("Rafraîchissement de la carte");
                        map.invalidateSize();
                    }, 100);
                    
                    // Variable pour stocker le marqueur
                    let marker;
                    
                    // Ajouter un gestionnaire de clic sur la carte
                    map.on('click', function(e) {
                        const { lat, lng } = e.latlng;
                        
                        // Mettre à jour les champs cachés pour latitude et longitude
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);
                        
                        // Ajouter ou déplacer le marqueur
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        } else {
                            marker = L.marker([lat, lng]).addTo(map);
                        }
                        
                        // Faire une recherche inverse d'adresse
                        reverseGeocode(lat, lng);
                    });
                    
                    // Fonction pour faire une recherche inverse d'adresse
                    function reverseGeocode(lat, lng) {
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.display_name) {
                                    // Mettre à jour le champ de localisation
                                    document.getElementById('location').value = data.display_name;
                                }
                            })
                            .catch(error => {
                                console.error("Erreur lors de la géolocalisation inverse:", error);
                            });
                    }
                    
                    // Ajouter un bouton de recherche de lieu
                    const locationInput = document.getElementById('location');
                    if (locationInput) {
                        // Créer un bouton de recherche s'il n'existe pas déjà
                        if (!document.querySelector('.location-search-button')) {
                            const searchButton = document.createElement('button');
                            searchButton.type = 'button';
                            searchButton.textContent = 'Rechercher ce lieu';
                            searchButton.className = 'location-search-button';
                            searchButton.style.marginTop = '5px';
                            searchButton.style.padding = '8px 12px';
                            searchButton.style.borderRadius = '4px';
                            searchButton.style.border = '1px solid #4c6ef5';
                            searchButton.style.backgroundColor = '#4c6ef5';
                            searchButton.style.color = 'white';
                            searchButton.style.cursor = 'pointer';
                            
                            // Insérer le bouton après le champ de localisation
                            locationInput.parentNode.insertBefore(searchButton, locationInput.nextSibling);
                            
                            // Ajouter un gestionnaire de clic pour rechercher
                            searchButton.addEventListener('click', function() {
                                const address = locationInput.value.trim();
                                if (address) {
                                    searchLocation(address);
                                }
                            });
                        }
                        
                        // Gérer la touche Entrée dans le champ de localisation
                        locationInput.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const address = this.value.trim();
                                if (address) {
                                    searchLocation(address);
                                }
                            }
                        });
                    }
                    
                    // Fonction pour rechercher un lieu
                    function searchLocation(address) {
                        // Afficher un indicateur de chargement
                        const searchLoader = document.createElement('div');
                        searchLoader.className = 'map-loading';
                        searchLoader.textContent = 'Recherche en cours...';
                        container.appendChild(searchLoader);
                        
                        // Faire une requête à Nominatim pour trouver le lieu
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                            .then(response => response.json())
                            .then(data => {
                                // Supprimer le loader
                                searchLoader.remove();
                                
                                if (data && data.length > 0) {
                                    const { lat, lon, display_name } = data[0];
                                    
                                    // Mettre à jour la vue de la carte
                                    map.setView([lat, lon], 14);
                                    
                                    // Mettre à jour les champs cachés
                                    document.getElementById('latitude').value = lat;
                                    document.getElementById('longitude').value = lon;
                                    
                                    // Ajouter ou déplacer le marqueur
                                    if (marker) {
                                        marker.setLatLng([lat, lon]);
                                    } else {
                                        marker = L.marker([lat, lon]).addTo(map);
                                    }
                                    
                                    // Mettre à jour le champ de localisation
                                    if (locationInput.value.trim() !== display_name) {
                                        locationInput.value = display_name;
                                    }
                                } else {
                                    // Afficher un message si aucun résultat n'est trouvé
                                    alert('Aucun lieu trouvé pour: ' + address);
                                }
                            })
                            .catch(error => {
                                searchLoader.remove();
                                console.error("Erreur lors de la recherche de lieu:", error);
                                alert('Erreur lors de la recherche de lieu. Veuillez réessayer.');
                            });
                    }
                    
                    console.log("Carte créée avec succès");
                    mapInitialized = true;
                    
                } catch (error) {
                    console.error("Erreur lors de la création de la carte:", error);
                    loader.textContent = "Erreur lors de la création de la carte";
                    loader.style.color = 'red';
                }
            }
            
            // Gestionnaire pour l'ouverture de la modal
            if (addHackathonBtn && hackathonRequestModal) {
                addHackathonBtn.addEventListener('click', function() {
                    // Afficher la modal
                    hackathonRequestModal.style.display = 'flex';
                    
                    // Attendre que la modal soit visible avant d'initialiser la carte
                    setTimeout(() => {
                        if (!mapInitialized) {
                            initMap();
                        } else {
                            // Si la carte est déjà initialisée, forcer un rafraîchissement
                            if (window.L && window.L.map) {
                                const mapInstance = window.L.map._instances ? 
                                    window.L.map._instances[0] : 
                                    window.L.map;
                                
                                if (mapInstance && typeof mapInstance.invalidateSize === 'function') {
                                    console.log("Rafraîchissement de la carte existante");
                                    mapInstance.invalidateSize();
                                }
                            }
                        }
                    }, 300);
                });
                
                // Gestionnaires pour fermer la modal
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', function() {
                        hackathonRequestModal.style.display = 'none';
                    });
                }
                
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        hackathonRequestModal.style.display = 'none';
                    });
                }
                
                // Gestion de la soumission du formulaire
                const submitBtn = document.getElementById('submit-hackathon-request');
                const requestForm = document.getElementById('hackathon-request-form');
                
                if (submitBtn && requestForm) {
                    submitBtn.addEventListener('click', function() {
                        requestForm.submit();
                    });
                }
            }
        });
    </script>
</body>
</html>