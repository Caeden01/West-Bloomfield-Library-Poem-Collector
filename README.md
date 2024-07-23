# West Bloomfield Library Poem Collector Website

Portal for anyone to be able to send in poems to their local library.

## Features

### Poem Portal
![Poem Portal](https://github.com/user-attachments/assets/fed1b1fe-77e8-45ad-bcb2-8bd36e69eba2)

Place where users can type and draft up their poems for submission!

### Saved Poems
![Saved Poems](https://github.com/user-attachments/assets/70f8761d-7c51-4f6e-8a54-7daa95abe29c)

Place where users can view their poems and keep themselves updated about status updates and see comments!

### Blog
![Blog](https://github.com/user-attachments/assets/194f15c0-b3b0-43e1-8177-4db939925b79)

Blog about how the team put together the project and the journey they went through to construct it

### FAQ
![FAQ](https://github.com/user-attachments/assets/f1911871-e186-4c6b-93e4-da885fb5402e)

FAQ page to answer possible questions users may have.

### Contact Page
![Contact Page](https://github.com/user-attachments/assets/2de728c5-f57e-4d58-8fc1-8e1945950f1b)

Page to contact moderators on the site about issues they may experience.

### Login Page
![Login Page](https://github.com/user-attachments/assets/d5c69154-6aff-49b5-9437-714dd00a37a5)

Page for moderators to log into.

### Poem Swiper
https://github.com/user-attachments/assets/f82e211c-45de-4a72-909e-91b1df1418a0

Quick site for moderators to quickly and conveniently view and review poems that are pending approval.

### Poem Database
![Poem Database](https://github.com/user-attachments/assets/5092475c-7190-4608-a359-2116a8557ff6)
![Poem Database](https://github.com/user-attachments/assets/626702ab-643b-44c1-ae49-99d2e5cc82a4)

Detailed database for moderators to search, move, delete, and queue poems for print.

### Manage Moderators
![Manage Moderators](https://github.com/user-attachments/assets/4add147d-5a38-414e-af24-1619faadc912)

Place for high-tier moderators to add additional moderators.

### Class Rank System

- **Low Tier Users** - Can only access Poem Swiper. No additional privileges.
- **Medium Tier Users** - Can access Poem Swiper, Detailed Database, Reported Issues, and Printer Queue. The only privilege they do not have access to is creating new user accounts.
- **High Tier Users** - Access to all features.

High-tier users have control over the accounts they create. At any time they can decide to ban users by navigating to "Manage Moderators" and clicking ban over the account of their choice.

### Printer Queue
![Printer Queue](https://github.com/user-attachments/assets/a5c444dd-e187-409a-9815-b56a83c32170)

Place to manage the order of poems that will be printed.

## Setup Instructions

1. Clone this repository
    ```bash
    git clone https://github.com/Caeden01/West-Bloomfield-Library-Poem-Collector
    ```

2. Create a MySQL database "wb_library_poem_project". Using phpMyAdmin, copy the following script.
    ```sql
    CREATE TABLE `issue_tickets` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `name` text NOT NULL,
      `email` text NOT NULL,
      `subject` text NOT NULL,
      `issue` text NOT NULL,
      `status` enum('open','closed') NOT NULL DEFAULT 'open',
      `securityInformation` text NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `poems` (
      `id` varchar(255) NOT NULL PRIMARY KEY,
      `name` text NOT NULL,
      `email` text NOT NULL,
      `title` text NOT NULL,
      `poem` text NOT NULL,
      `timestamp` text NOT NULL,
      `securityInformation` text NOT NULL,
      `history` text NOT NULL,
      `comments` mediumtext NOT NULL,
      `commentSecurityInformation` mediumtext NOT NULL,
      `printHistory` mediumtext NOT NULL,
      `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `printer_queue` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `title` text NOT NULL,
      `author` text NOT NULL,
      `added_by` text NOT NULL,
      `poem_id` text NOT NULL,
      `queue_number` int(11) NOT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `username` varchar(255) NOT NULL UNIQUE,
      `password` varchar(255) DEFAULT NULL,
      `parent_id` int(11) DEFAULT NULL,
      `tier` enum('low','medium','high','') NOT NULL,
      `banned` tinyint(4) DEFAULT 0,
      KEY `parent_id` (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ```

3. Set up a moderator account. Replace YOUR_ACCOUNT_NAME with your name and then copy and paste the script into phpMyAdmin.
    ```sql
    INSERT INTO `users` (`username`, `parent_id`, `tier`, `banned`)
    VALUES ('YOUR_ACCOUNT_NAME', NULL, 'high', 0);
    ```

4. Enter the file "include.php" and make the following changes:
    1. Enter your MySQL database credentials into "$username" and "$password".
    2. Set up an authorization token — make sure it matches the one you set up here (https://github.com/Caeden01/wb-library-poem-collector-printer-drivers)
    3. Create a QR code for the URL the project is hosted on. Include that QR code under "./images/qr_code.png". If you store at that specific path, there is no need to update the variable "website_qr_code".
    4. Set up your library config information ("library_name", "library_logo", "library_logo_bw", "library_website", "website_qr_code", and "url_comment_path")
    5. (Optional) Set up Google reCAPTCHA.
        - Set "use_captcha" to true
        - Enter your reCAPTCHA public and private keys under "captcha_public_key" and "captcha_private_key"

5. Finish setting up your moderator account
    1. Visit the website URL
    2. Navigate to the login page
    3. Sign in like you would if you had a password configured and it will set up that password automatically.

6. Set up printer
   
    Follow the instruction guide here: https://github.com/Caeden01/wb-library-poem-collector-printer-drivers

## TO DO

- [ ] Clean Up - remove redundancies and make the code more clean and optimized as a whole.
- [ ] Create email notification API - notify users about any status updates on their poems.
- [ ] Add Category System - allow poems to be filtered based on their genre.
