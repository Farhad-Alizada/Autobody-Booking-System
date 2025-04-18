-- database_setup.sql  (image‑ready version)

-- 1. Create the database and use it
DROP DATABASE IF EXISTS AutoBodyBooking;
CREATE DATABASE AutoBodyBooking;
USE AutoBodyBooking;

-- 2. Users
CREATE TABLE IF NOT EXISTS Users (
                                     UserID       INT AUTO_INCREMENT PRIMARY KEY,
                                     Password     VARCHAR(255) NOT NULL,
                                     PhoneNumber  VARCHAR(20),
                                     FirstName    VARCHAR(30),
                                     LastName     VARCHAR(30),
                                     Email        VARCHAR(50) NOT NULL UNIQUE,
                                     AccessLevel  ENUM('Admin','Employee','Customer') NOT NULL,
                                     DateCreated  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Admin
CREATE TABLE IF NOT EXISTS Admin (
                                     UserID            INT PRIMARY KEY,
                                     WebsiteUpdateDate DATE,
                                     AdminNotes        TEXT,
                                     FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 4. Employee
CREATE TABLE IF NOT EXISTS Employee (
                                        UserID         INT PRIMARY KEY,
                                        JobTitle       VARCHAR(50),
                                        Specialization VARCHAR(150),
                                        FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 5. Customer
CREATE TABLE IF NOT EXISTS Customer (
                                        UserID           INT PRIMARY KEY,
                                        PreferredContact VARCHAR(20),
                                        Address          VARCHAR(255),
                                        FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 6. ServiceOffering (★ now stores image path)
CREATE TABLE IF NOT EXISTS ServiceOffering (
                                               OfferingID         INT AUTO_INCREMENT PRIMARY KEY,
                                               OfferingName       VARCHAR(100),
                                               ServiceDescription TEXT,
                                               ServicePrice       DECIMAL(10,2),
                                               ImagePath          VARCHAR(255)
);

-- 7. Feedback
CREATE TABLE IF NOT EXISTS Feedback (
                                        FeedbackID     INT AUTO_INCREMENT PRIMARY KEY,
                                        CustomerUserID INT,
                                        FeedbackDate   DATETIME,
                                        FeedbackName   VARCHAR(50) DEFAULT NULL,
                                        Comments       TEXT,
                                        Rating         INT,
                                        FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID)
);

-- 8. DiscountCoupon
CREATE TABLE IF NOT EXISTS DiscountCoupon (
                                              CouponNumber   INT AUTO_INCREMENT PRIMARY KEY,
                                              DiscountAmount DECIMAL(10,2),
                                              OfferingID     INT,
                                              AdminUserID    INT,
                                              FOREIGN KEY (OfferingID) REFERENCES ServiceOffering(OfferingID),
                                              FOREIGN KEY (AdminUserID) REFERENCES Admin(UserID)
);

-- 9. Schedule (Bookings)
CREATE TABLE IF NOT EXISTS Schedule (
                                        CustomerUserID INT,
                                        OfferingID     INT,
                                        StartDate      DATETIME,
                                        EndDate        DATETIME,
                                        AdminUserID    INT,
                                        Status         ENUM('Scheduled','In Progress','Completed') DEFAULT 'Scheduled',
                                        PRIMARY KEY (CustomerUserID,OfferingID,StartDate,EndDate),
                                        FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID),
                                        FOREIGN KEY (OfferingID)     REFERENCES ServiceOffering(OfferingID),
                                        FOREIGN KEY (AdminUserID)    REFERENCES Admin(UserID)
);

-- 10. CustomerDiscountCoupon
CREATE TABLE IF NOT EXISTS CustomerDiscountCoupon (
                                                      CouponNumber   INT,
                                                      CustomerUserID INT,
                                                      PRIMARY KEY (CouponNumber,CustomerUserID),
                                                      FOREIGN KEY (CouponNumber)   REFERENCES DiscountCoupon(CouponNumber),
                                                      FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID)
);

-- 11. ScheduleEmployee
CREATE TABLE IF NOT EXISTS ScheduleEmployee (
                                                CustomerUserID INT,
                                                OfferingID     INT,
                                                StartDate      DATETIME,
                                                EndDate        DATETIME,
                                                EmployeeUserID INT,
                                                PRIMARY KEY (CustomerUserID,OfferingID,StartDate,EndDate,EmployeeUserID),
                                                FOREIGN KEY (CustomerUserID,OfferingID,StartDate,EndDate)
                                                    REFERENCES Schedule(CustomerUserID,OfferingID,StartDate,EndDate),
                                                FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- 12. DealsWith
CREATE TABLE IF NOT EXISTS DealsWith (
                                         CustomerUserID INT,
                                         EmployeeUserID INT,
                                         PRIMARY KEY (CustomerUserID,EmployeeUserID),
                                         FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID),
                                         FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- 13. EmployeeAvailability
CREATE TABLE IF NOT EXISTS EmployeeAvailability (
                                                    AvailabilityID   INT AUTO_INCREMENT PRIMARY KEY,
                                                    EmployeeUserID   INT,
                                                    AvailabilityDate DATE,
                                                    StartTime        TIME,
                                                    EndTime          TIME,
                                                    FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- =========================================================
-- Sample data
-- =========================================================

-- Admin
INSERT INTO Users (Password,PhoneNumber,FirstName,LastName,Email,AccessLevel)
VALUES ('adminpass','587-000-1111','Ahmed','Chaudhry','ahmedch45@admin.com','Admin');
INSERT INTO Admin (UserID,WebsiteUpdateDate,AdminNotes)
VALUES (1,'2025-03-18','Main Admin account');

-- Employee
INSERT INTO Users (Password,PhoneNumber,FirstName,LastName,Email,AccessLevel)
VALUES ('employeepass','587-111-000','Richard','Tan','richardtan5789@company.com','Employee');
INSERT INTO Employee (UserID,JobTitle,Specialization)
VALUES (2,'Mechanic','Auto Repair');

-- Customer
INSERT INTO Users (Password,PhoneNumber,FirstName,LastName,Email,AccessLevel)
VALUES ('customerpass','825-111-000','Charlie','Angus','charlie@example.com','Customer');
INSERT INTO Customer (UserID,PreferredContact,Address)
VALUES (3,'Email','123 Main Street');

-- Services
INSERT INTO ServiceOffering (OfferingName,ServiceDescription,ServicePrice,ImagePath)
VALUES
    ('Vinyl Wrap','Full vehicle vinyl wrapping service',500.00,NULL),
    ('Window Tint','Professional window tinting',200.00,NULL),
    ('Performance Tuning','Enhance vehicle performance with ECU tuning',350.00,NULL),
    ('PPF','Paint Protection Film application',450.00,NULL);

-- DealsWith
INSERT INTO DealsWith (CustomerUserID,EmployeeUserID) VALUES (3,2);

-- Schedule
INSERT INTO Schedule (CustomerUserID,OfferingID,StartDate,EndDate,AdminUserID,Status)
VALUES (3,1,'2025-03-25 10:00:00','2025-03-25 11:00:00',1,'Scheduled');

-- ScheduleEmployee
INSERT INTO ScheduleEmployee
(CustomerUserID,OfferingID,StartDate,EndDate,EmployeeUserID)
VALUES (3,1,'2025-03-25 10:00:00','2025-03-25 11:00:00',2);

-- Feedback
INSERT INTO Feedback
(CustomerUserID,FeedbackDate,FeedbackName,Comments,Rating)
VALUES (3,'2025-03-26 15:30:00','Charlie','Great service!',5);

-- DiscountCoupon + assignment
INSERT INTO DiscountCoupon (DiscountAmount,OfferingID,AdminUserID)
VALUES (5.00,1,1);
INSERT INTO CustomerDiscountCoupon (CouponNumber,CustomerUserID) VALUES (1,3);

-- Sample SELECT Queries (Examples)

SELECT * FROM Users;
SELECT * FROM ServiceOffering;

SELECT U.FirstName, U.LastName, SO.OfferingName, S.StartDate, S.EndDate, S.Status
FROM Schedule S
         JOIN Customer C ON S.CustomerUserID = C.UserID
         JOIN Users U ON C.UserID = U.UserID
         JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID;

SELECT U.FirstName, U.LastName, F.FeedbackName, F.Comments, F.Rating
FROM Feedback F
         JOIN Customer C ON F.CustomerUserID = C.UserID
         JOIN Users U ON C.UserID = U.UserID;

-- Sample UPDATE Queries

UPDATE Users
SET PhoneNumber = '647-555-1985'
WHERE UserID = 3;

UPDATE ServiceOffering
SET ServicePrice = 22.00
WHERE OfferingID = 1;

-- Sample DELETE Queries

DELETE FROM Feedback
WHERE FeedbackID = 1;

DELETE FROM CustomerDiscountCoupon
WHERE CouponNumber = 1 AND CustomerUserID = 3;

-- Aggregation

SELECT CustomerUserID, COUNT(*) AS BookingCount
FROM Schedule
GROUP BY CustomerUserID;

-- Subquery

SELECT U.FirstName, U.LastName, U.Email
FROM Users U
WHERE U.UserID IN (
    SELECT CustomerUserID
    FROM Schedule
    WHERE StartDate >= '2025-03-25'
);

-- Join with GROUP BY

SELECT U.FirstName, U.LastName, F.Comments, F.Rating
FROM Feedback F
         JOIN Customer C ON F.CustomerUserID = C.UserID
         JOIN Users U ON C.UserID = U.UserID
         JOIN Schedule S ON F.CustomerUserID = S.CustomerUserID
GROUP BY F.FeedbackID, U.FirstName, U.LastName, F.Comments, F.Rating;

-- Update with Join

UPDATE ServiceOffering SO
    JOIN DiscountCoupon DC ON SO.OfferingID = DC.OfferingID
SET SO.ServicePrice = SO.ServicePrice - DC.DiscountAmount
WHERE DC.CouponNumber = 1;

-- Delete with Subquery

DELETE FROM Schedule
WHERE CustomerUserID IN (
    SELECT UserID FROM Users WHERE Email LIKE '%@test.com'
);

-- View

CREATE VIEW CustomerBookings AS
SELECT U.UserID, U.FirstName, U.LastName, S.OfferingID, S.StartDate, S.EndDate
FROM Users U
         JOIN Customer C ON U.UserID = C.UserID
         JOIN Schedule S ON C.UserID = S.CustomerUserID;
