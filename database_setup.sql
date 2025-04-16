-- database_setup.sql


-- 1. Create the Database and Use It
CREATE DATABASE IF NOT EXISTS AutoBodyBooking;
USE AutoBodyBooking;

-- 2. Create the Users Table (General User Information)
CREATE TABLE Users (
    UserID         INT AUTO_INCREMENT PRIMARY KEY,
    Password       VARCHAR(255) NOT NULL,
    PhoneNumber    VARCHAR(20),
    FirstName      VARCHAR(30),
    LastName       VARCHAR(30),
    Email          VARCHAR(50) NOT NULL UNIQUE,
    AccessLevel    ENUM('Admin', 'Employee', 'Customer') NOT NULL,
    DateCreated    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Create the Admin Table (Specialized Admin Attributes)
CREATE TABLE Admin (
    UserID              INT PRIMARY KEY,
    WebsiteUpdateDate   DATE,
    AdminNotes          TEXT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 4. Create the Employee Table (Specialized Employee Attributes)
CREATE TABLE Employee (
    UserID         INT PRIMARY KEY,
    JobTitle       VARCHAR(50),
    Specialization VARCHAR(150),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 5. Create the Customer Table (Specialized Customer Attributes)
CREATE TABLE Customer (
    UserID           INT PRIMARY KEY,
    PreferredContact VARCHAR(20),
    Address          VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- 6. Create the Service Offering Table
CREATE TABLE ServiceOffering (
    OfferingID          INT AUTO_INCREMENT PRIMARY KEY,
    OfferingName        VARCHAR(100),
    ServiceDescription  TEXT,
    ServicePrice        DECIMAL(10,2),
    TotalPrice          DECIMAL(10,2)
);

-- 7. Create the Feedback Table (Customer Feedback)
CREATE TABLE Feedback (
    FeedbackID     INT AUTO_INCREMENT PRIMARY KEY,
    CustomerUserID INT,
    FeedbackDate   DATETIME,
    FeedbackName   VARCHAR(50) DEFAULT NULL,
    Comments       TEXT,
    Rating         INT,
    FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID)
);

-- 8. Create the DiscountCoupon Table
CREATE TABLE DiscountCoupon (
    CouponNumber    INT AUTO_INCREMENT PRIMARY KEY,
    DiscountAmount  DECIMAL(10,2),
    OfferingID      INT,
    AdminUserID     INT,
    FOREIGN KEY (OfferingID) REFERENCES ServiceOffering(OfferingID),
    FOREIGN KEY (AdminUserID) REFERENCES Admin(UserID)
);

-- 9. Create the Schedule Table (Bookings)
--     Now includes a 'Status' column for appointment tracking.
CREATE TABLE Schedule (
    CustomerUserID  INT,
    OfferingID      INT,
    StartDate       DATETIME,
    EndDate         DATETIME,
    TotalPrice      DECIMAL(10,2),
    AdminUserID     INT,
    Status          ENUM('Scheduled','In Progress','Completed') DEFAULT 'Scheduled',
    PRIMARY KEY (CustomerUserID, OfferingID, StartDate, EndDate),
    FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID),
    FOREIGN KEY (OfferingID) REFERENCES ServiceOffering(OfferingID),
    FOREIGN KEY (AdminUserID) REFERENCES Admin(UserID)
);

-- 10. Create the CustomerDiscountCoupon Table (Many-to-Many between DiscountCoupon and Customer)
CREATE TABLE CustomerDiscountCoupon (
    CouponNumber    INT,
    CustomerUserID  INT,
    PRIMARY KEY (CouponNumber, CustomerUserID),
    FOREIGN KEY (CouponNumber) REFERENCES DiscountCoupon(CouponNumber),
    FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID)
);

-- 11. Create the ScheduleEmployee Table (Many-to-Many between Schedule and Employee)
CREATE TABLE ScheduleEmployee (
    CustomerUserID  INT,
    OfferingID      INT,
    StartDate       DATETIME,
    EndDate         DATETIME,
    EmployeeUserID  INT,
    PRIMARY KEY (CustomerUserID, OfferingID, StartDate, EndDate, EmployeeUserID),
    FOREIGN KEY (CustomerUserID, OfferingID, StartDate, EndDate)
        REFERENCES Schedule(CustomerUserID, OfferingID, StartDate, EndDate),
    FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- 12. Create the DealsWith Table (Many-to-Many Relationship Between Customer and Employee)
CREATE TABLE DealsWith (
    CustomerUserID  INT,
    EmployeeUserID  INT,
    PRIMARY KEY (CustomerUserID, EmployeeUserID),
    FOREIGN KEY (CustomerUserID) REFERENCES Customer(UserID),
    FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- 13. Create the EmployeeAvailability Table
--     This table stores the available time slots set by an employee.
CREATE TABLE EmployeeAvailability (
    AvailabilityID     INT AUTO_INCREMENT PRIMARY KEY,
    EmployeeUserID     INT,
    AvailabilityDate   DATE,
    StartTime          TIME,
    EndTime            TIME,
    FOREIGN KEY (EmployeeUserID) REFERENCES Employee(UserID)
);

-- Sample INSERT Statements (Examples)

-- 1. Insert an Admin user
INSERT INTO Users (Password, PhoneNumber, FirstName, LastName, Email, AccessLevel)
VALUES ('adminpass', '587-000-1111', 'Ahmed', 'Chaudhry', 'ahmedch45@admin.com', 'Admin');

-- Assuming Admin's UserID is 1
INSERT INTO Admin (UserID, WebsiteUpdateDate, AdminNotes)
VALUES (1, '2025-03-18', 'Main Admin account');

-- 2. Insert an Employee user
INSERT INTO Users (Password, PhoneNumber, FirstName, LastName, Email, AccessLevel)
VALUES ('employeepass', '587-111-000', 'Richard', 'Tan', 'richardtan5789@company.com', 'Employee');

-- Assuming Employee's UserID is 2
INSERT INTO Employee (UserID, JobTitle, Specialization)
VALUES (2, 'Mechanic', 'Auto Repair');

-- 3. Insert a Customer user
INSERT INTO Users (Password, PhoneNumber, FirstName, LastName, Email, AccessLevel)
VALUES ('customerpass', '825-111-000', 'Charlie', 'Angus', 'charlie@example.com', 'Customer');

-- Assuming Customer's UserID is 3
INSERT INTO Customer (UserID, PreferredContact, Address)
VALUES (3, 'Email', '123 Main Street');

-- 4. Insert a Service Offering (Assuming new OfferingID is 1)
INSERT INTO ServiceOffering (OfferingName, ServiceDescription, ServicePrice, TotalPrice)
VALUES 
  ('Vinyl Wrap', 'Full vehicle vinyl wrapping service', 500.00, 500.00),
  ('Window Tint', 'Professional window tinting', 200.00, 200.00),
  ('Performance Tuning', 'Enhance vehicle performance with ECU tuning', 350.00, 350.00),
  ('PPF', 'Paint Protection Film application', 450.00, 450.00);

-- 5. Insert into DealsWith (linking Customer (UserID 3) with Employee (UserID 2))
INSERT INTO DealsWith (CustomerUserID, EmployeeUserID)
VALUES (3, 2);

-- 6. Insert a Schedule (Booking) with Status (Assuming status defaults to 'Scheduled')
INSERT INTO Schedule (CustomerUserID, OfferingID, StartDate, EndDate, TotalPrice, AdminUserID, Status)
VALUES (3, 1, '2025-03-25 10:00:00', '2025-03-25 11:00:00', 20.00, 1, 'Scheduled');

-- 7. Insert into ScheduleEmployee (linking the schedule to an employee)
INSERT INTO ScheduleEmployee (CustomerUserID, OfferingID, StartDate, EndDate, EmployeeUserID)
VALUES (3, 1, '2025-03-25 10:00:00', '2025-03-25 11:00:00', 2);

-- 8. Insert Feedback from a customer, capturing a name as well
INSERT INTO Feedback (CustomerUserID, FeedbackDate, FeedbackName, Comments, Rating)
VALUES (3, '2025-03-26 15:30:00', 'Charlie', 'Great service!', 5);

-- 9. Insert a DiscountCoupon (Assuming new CouponNumber is 1)
INSERT INTO DiscountCoupon (DiscountAmount, OfferingID, AdminUserID)
VALUES (5.00, 1, 1);

-- 10. Link a DiscountCoupon to a Customer
INSERT INTO CustomerDiscountCoupon (CouponNumber, CustomerUserID)
VALUES (1, 3);

-- Sample SELECT Queries (Examples)

-- Retrieve all users
SELECT * FROM Users;

-- Retrieve all service offerings
SELECT * FROM ServiceOffering;

-- Retrieve schedule details with customer name and service offering
SELECT U.FirstName, U.LastName, SO.OfferingName, S.StartDate, S.EndDate, S.TotalPrice, S.Status
FROM Schedule S
JOIN Customer C ON S.CustomerUserID = C.UserID
JOIN Users U ON C.UserID = U.UserID
JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID;

-- Retrieve feedback details along with customer information
SELECT U.FirstName, U.LastName, F.FeedbackName, F.Comments, F.Rating
FROM Feedback F
JOIN Customer C ON F.CustomerUserID = C.UserID
JOIN Users U ON C.UserID = U.UserID;

-- Sample UPDATE Queries (Examples)

-- Update a user's phone number (for UserID 3)
UPDATE Users
SET PhoneNumber = '647-555-1985'
WHERE UserID = 3;

UPDATE ServiceOffering
SET ServicePrice = 22.00, TotalPrice = 22.00
WHERE OfferingID = 1;

-- Sample DELETE Queries (Examples)

-- Delete a feedback entry (FeedbackID 1)
DELETE FROM Feedback
WHERE FeedbackID = 1;

-- Remove a DiscountCoupon assignment from a customer
DELETE FROM CustomerDiscountCoupon
WHERE CouponNumber = 1 AND CustomerUserID = 3;

-- Aggregate & Group By Example

-- Count the number of bookings (schedules) per customer
SELECT CustomerUserID, COUNT(*) AS BookingCount
FROM Schedule
GROUP BY CustomerUserID;

-- Subquery Example: Retrieve names and emails of users with a booking on/after a date

SELECT U.FirstName, U.LastName, U.Email
FROM Users U
WHERE U.UserID IN (
    SELECT CustomerUserID
    FROM Schedule
    WHERE StartDate >= '2025-03-25'
);

-- Join in SELECT Example: List all feedback with customer's full name and booking total price

SELECT U.FirstName, U.LastName, F.Comments, F.Rating, S.TotalPrice
FROM Feedback F
JOIN Customer C ON F.CustomerUserID = C.UserID
JOIN Users U ON C.UserID = U.UserID
JOIN Schedule S ON F.CustomerUserID = S.CustomerUserID
GROUP BY F.FeedbackID;

-- Update with Join Example: Reduce a service offering's price by the discount amount from a specific coupon

UPDATE ServiceOffering SO
JOIN DiscountCoupon DC ON SO.OfferingID = DC.OfferingID
SET SO.ServicePrice = SO.ServicePrice - DC.DiscountAmount,
    SO.TotalPrice = SO.ServicePrice - DC.DiscountAmount
WHERE DC.CouponNumber = 1;

-- Subquery in UPDATE Example: Increase the total price of a service offering by 10%

UPDATE ServiceOffering
SET TotalPrice = ServicePrice * 1.10
WHERE OfferingID = 1;

-- Delete with Subquery Example: Delete schedules for customers whose email domain is 'test.com'

DELETE FROM Schedule
WHERE CustomerUserID IN (
    SELECT UserID FROM Users WHERE Email LIKE '%@test.com'
);

-- Alter Table Example: Add a new column to the Users table to store the account creation date (if not already added)

-- ALTER TABLE Users
-- ADD COLUMN DateCreated DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Create View Example: Customer bookings with user details


CREATE VIEW CustomerBookings AS
SELECT U.UserID, U.FirstName, U.LastName, S.OfferingID, S.StartDate, S.EndDate, S.TotalPrice
FROM Users U
JOIN Customer C ON U.UserID = C.UserID
JOIN Schedule S ON C.UserID = S.CustomerUserID;
