# flownote
This is a basic note sharing website, nothing fancy. Just based on vanilla css and some js, and for backend, there is php. 
I used Apache Server and for database, I went for mySQL, which in turn prompted me to use XAMPP as my configuration and logging tool.

The whole project was a test run for php learning/practice. For example, I tried a very unconventional file storage method, and that is storing the file in database. I know it is not the best way, if I could I would do it in s3. Didn't like the idea of storing in local storage. So did that. For that, I changed the config a little.

### changes in php.ini
upload_max_filesize = 50M\
post_max_size = 50M\
max_execution_time = 300\
memory_limit = 256M

### changes in mysql ini(my.ini)
max_allowed_packet = 50M\
wait_timeout = 600\
interactive_timeout = 600  

## Table Schemas


### Create the database and users table
CREATE DATABASE IF NOT EXISTS my_db;\
USE my_db;\

CREATE TABLE IF NOT EXISTS users (\
    id INT AUTO_INCREMENT PRIMARY KEY,\
    name VARCHAR(100) NOT NULL,\
    email VARCHAR(255) UNIQUE NOT NULL,\
    password VARCHAR(255) NOT NULL,\
    batch VARCHAR(50),\
    section VARCHAR(50),\
    token VARCHAR(32),\
    is_verified TINYINT(1) DEFAULT 0,\
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\
);

-- Optional: Create an index on email for faster lookups\
CREATE INDEX idx_email ON users(email);\
CREATE INDEX idx_token ON users(token);

### Create table for uploading files
CREATE TABLE uploaded_files (\
    id INT AUTO_INCREMENT PRIMARY KEY,\
    user_id INT NOT NULL,\
    original_name VARCHAR(255) NOT NULL,\
    file_size BIGINT NOT NULL,\
    file_type VARCHAR(100),\
    note_type ENUM('personal', 'shared') DEFAULT 'personal',\
    category VARCHAR(50) DEFAULT 'general',\
    description TEXT NULL,\
    file_content LONGTEXT NULL,\
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\
    INDEX idx_user_id (user_id),\
    INDEX idx_category (category),\
    INDEX idx_note_type (note_type),\
    INDEX idx_upload_date (upload_date)\
);
