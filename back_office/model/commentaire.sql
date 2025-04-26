CREATE TABLE commentaire (
    idcommentaire INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    createdat DATETIME DEFAULT CURRENT_TIMESTAMP,
    idnews INT NOT NULL,
    FOREIGN KEY (idnews) REFERENCES news(idnews) ON DELETE CASCADE
);
