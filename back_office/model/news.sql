CREATE TABLE news (
    idnews INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    author VARCHAR(150) NOT NULL,
    content LONGTEXT NOT NULL,
    newscategory VARCHAR(100) NOT NULL,
    image TEXT NOT NULL,
    created_at DATE,
    status VARCHAR(100) NOT NULL
);
