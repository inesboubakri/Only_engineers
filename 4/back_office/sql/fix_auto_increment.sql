-- Execute this SQL in phpMyAdmin to fix the AUTO_INCREMENT issue

-- Reset AUTO_INCREMENT counter
ALTER TABLE `projet` AUTO_INCREMENT = 1;

-- If needed, you can also modify the AUTO_INCREMENT column to make sure it's properly set
ALTER TABLE `projet` MODIFY `AUTO_INCREMENT` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;
