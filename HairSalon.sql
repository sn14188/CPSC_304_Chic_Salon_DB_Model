drop table GetServiced;
drop table Feedback1;
drop table Feedback2;
drop table Payment1;
drop table Payment2;
drop table Appointment;
drop table Assist_StylistAssistant;
drop table HasMany_Stylist1;
drop table Has_Receptionist;
drop table Styling;
drop table Haircut;
drop table Coloring;
drop table Service;
drop table HasMany_Stylist2;
drop table Customer;
drop table HairSalon;

CREATE TABLE HairSalon
(
    salonName CHAR(20),
    address CHAR(50),
    phoneNum CHAR(13),
    PRIMARY KEY (salonName)
);

CREATE TABLE HasMany_Stylist2
(
    baseSalary FLOAT,
    bonus FLOAT,
    totalSalary FLOAT,
    PRIMARY KEY (baseSalary, bonus)
);

CREATE TABLE Service
(
    serviceID CHAR(6),
    price FLOAT,
    duration INTEGER,
    PRIMARY KEY (serviceID)
);

CREATE TABLE Styling
(
    serviceID CHAR(6),
    hairDry CHAR(1),
    curling CHAR(1),
    straightening CHAR(1),
    FOREIGN KEY (serviceID) REFERENCES Service(serviceID) ON DELETE CASCADE,
    PRIMARY KEY (serviceID)
);

CREATE TABLE Haircut
(
    serviceID CHAR(6),
    cutStyle CHAR(20),
    FOREIGN KEY (serviceID) REFERENCES Service(serviceID) ON DELETE CASCADE,
    PRIMARY KEY (serviceID)
);

CREATE TABLE Coloring
(
    serviceID CHAR(6),
    color CHAR(20),
    FOREIGN KEY (serviceID) REFERENCES Service(serviceID) ON DELETE CASCADE,
    PRIMARY KEY (serviceID)
);

CREATE TABLE Has_Receptionist
(
    rID INTEGER,
    name CHAR(20),
    phoneNum CHAR(13),
    salonName CHAR(20) NOT NULL,
    PRIMARY KEY (rID),
    FOREIGN KEY (salonName) REFERENCES HairSalon(salonName)
);

CREATE TABLE HasMany_Stylist1
(
    sID INTEGER,
    name CHAR(20),
    phoneNum CHAR(13),
    baseSalary FLOAT,
    bonus FLOAT,
    salonName CHAR(20) NOT NULL,
    PRIMARY KEY (sID),
    FOREIGN KEY (salonName) REFERENCES HairSalon(salonName),
    FOREIGN KEY (baseSalary, bonus) REFERENCES HasMany_Stylist2(baseSalary, bonus)
);

CREATE TABLE Assist_StylistAssistant
(
    saID INTEGER,
    name CHAR(20),
    phoneNum CHAR(13),
    sID INTEGER NOT NULL,
    PRIMARY KEY (saID, sID),
    FOREIGN KEY (sID) REFERENCES HasMany_Stylist1(sID)
);

CREATE TABLE Customer
(
    cID INTEGER,
    name CHAR(20),
    gender CHAR(10),
    phoneNum CHAR(13),
    lastVisit INTEGER,
    PRIMARY KEY (cID)
);

CREATE TABLE Payment2
(
    baseAmount FLOAT,
    tipAmount FLOAT,
    totalAmount FLOAT,
    PRIMARY KEY (baseAmount, tipAmount)
);

CREATE TABLE Payment1
(
    transactionNum INTEGER,
    method CHAR(20),
    baseAmount FLOAT,
    tipAmount FLOAT,
    rID INTEGER NOT NULL,
    cID INTEGER NOT NULL,
    PRIMARY KEY (transactionNum),
    FOREIGN KEY (rID) REFERENCES Has_Receptionist(rID),
    FOREIGN KEY (cID) REFERENCES Customer(cID),
    FOREIGN KEY (baseAmount, tipAmount) REFERENCES Payment2(baseAmount, tipAmount)
);

CREATE TABLE Feedback2
(
    rate INTEGER CHECK (rate >=1 AND rate <=10),
    sentiment CHAR(1),
    PRIMARY KEY (rate)
);

CREATE TABLE Feedback1
(
    feedbackNum CHAR(5),
    rate INTEGER NOT NULL,
    rID INTEGER NOT NULL,
    cID INTEGER NOT NULL,
    PRIMARY KEY (feedbackNum),
    FOREIGN KEY (rID) REFERENCES Has_Receptionist(rID),
    FOREIGN KEY (cID) REFERENCES Customer(cID),
    FOREIGN KEY (rate) REFERENCES Feedback2(rate)
);

CREATE TABLE GetServiced
(
    serviceNum CHAR(6) NOT NULL,
    cID INTEGER NOT NULL,
    PRIMARY KEY (serviceNum, cID),
    FOREIGN KEY (cID) REFERENCES Customer(cID),
    FOREIGN KEY (serviceNum) REFERENCES Service(serviceID)
);

CREATE TABLE Appointment
(
    confirmationNum INTEGER,
    a_date INTEGER,
    a_time INTEGER,
    rID INTEGER NOT NULL,
    cID INTEGER NOT NULL,
    PRIMARY KEY (confirmationNum),
    FOREIGN KEY (rID) REFERENCES Has_Receptionist(rID),
    FOREIGN KEY (cID) REFERENCES Customer(cID)
);



/* Insert lists: */
insert into HairSalon
values('Chic Salon', '123 Main Street, Cityville', '17784458899');
insert into HairSalon
values('Glamour Cuts', '456 Oak Avenue', '17783256890');
insert into HairSalon
values('Vintage Scissors', '987 Cedar Drive, Citrus Park', '17785554444');
insert into HairSalon
values('Modern Fringes', '258 Maple Court, Newtown', '17786662222');
insert into HairSalon
values('Elite Stylings', '159 Olive Lane, Cloud Peak', '17785556666');

insert into HasMany_Stylist2
values(30000, 2000, 32000);
insert into HasMany_Stylist2
values(34000, 1500, 35500);
insert into HasMany_Stylist2
values(35000, 1500, 36500);
insert into HasMany_Stylist2
values(38000, 2200, 40200);
insert into HasMany_Stylist2
values(30000, 3000, 33000);

insert into Service
values('STY001', 30, 30);
insert into Service
values('STY002', 30, 30);
insert into Service
values('STY003', 30, 30);
insert into Service
values('STY004', 30, 30);
insert into Service
values('STY005', 30, 30);
insert into Service
values('CUT001', 25, 25);
insert into Service
values('CUT002', 40, 40);
insert into Service
values('CUT003', 25, 25);
insert into Service
values('CUT004', 40, 40);
insert into Service
values('CUT005', 40, 40);
insert into Service
values('COL001', 50, 50);
insert into Service
values('COL002', 50, 50);
insert into Service
values('COL003', 50, 50);
insert into Service
values('COL004', 50, 50);
insert into Service
values('COL005', 50, 50);

insert into Styling
values('STY001', 'T', 'F', 'T');
insert into Styling
values('STY002', 'T', 'T', 'F');
insert into Styling
values('STY003', 'F', 'T', 'T');
insert into Styling
values('STY004', 'T', 'T', 'T');
insert into Styling
values('STY005', 'F', 'F', 'T');

insert into Haircut
values('CUT001', 'Pixie');
insert into Haircut
values('CUT002', 'Bob');
insert into Haircut
values('CUT003', 'Lob');
insert into Haircut
values('CUT004', 'Shag');
insert into Haircut
values('CUT005', 'Buzz');

insert into Coloring
values('COL001', 'Blonde');
insert into Coloring
values('COL002', 'Brunette');
insert into Coloring
values('COL003', 'Red');
insert into Coloring
values('COL004', 'Black');
insert into Coloring
values('COL005', 'Pink');

insert into Has_Receptionist
values(1, 'Anna Thompson', '17785559910', 'Chic Salon');
insert into Has_Receptionist
values(2, 'Lily Brooks', '17785559920', 'Glamour Cuts');
insert into Has_Receptionist
values(3, 'Jake Miller', '17785559930', 'Vintage Scissors');
insert into Has_Receptionist
values(4, 'Sophia Davis', '17785559940', 'Modern Fringes');
insert into Has_Receptionist
values(5, 'Oliver James', '17785559950', 'Elite Stylings');

insert into HasMany_Stylist1
values(1, 'John', '17785558901', 30000, 2000, 'Chic Salon');
insert into HasMany_Stylist1
values(2, 'Emily White', '17785558902', 30000, 2000, 'Chic Salon');
insert into HasMany_Stylist1
values(3, 'Robert Lee', '17785558903', 34000, 1500, 'Glamour Cuts');
insert into HasMany_Stylist1
values(4, 'Laura Brown', '17785558904', 35000, 1500, 'Vintage Scissors');
insert into HasMany_Stylist1
values(5, 'Mark Smith', '17785558905', 38000, 2200, 'Elite Stylings');
insert into HasMany_Stylist1
values(6, 'Jamie Angel', '17785558906', 30000, 3000, 'Chic Salon');

insert into Assist_StylistAssistant
values(1, 'Ella Harris', '17785558801', 1);
insert into Assist_StylistAssistant
values(2, 'Lucas Wilson', '17785558802', 1);
insert into Assist_StylistAssistant
values(3, 'Amelia Martin', '17785558803', 3);
insert into Assist_StylistAssistant
values(4, 'Noah Taylor', '17785558804', 4);
insert into Assist_StylistAssistant
values(5, 'Ava Anderson', '17785558805', 1);

insert into Customer
values(1, 'Bob', 'male', '17782236654', 230701);
insert into Customer
values(2, 'Cassie', 'female', '17782236655', 230628);
insert into Customer
values(3, 'Sandra', 'female', '17782236656', 220101);
insert into Customer
values(4, 'Kris', 'female', '17782236657', 230725);
insert into Customer
values(5, 'Sam', 'non-binary', '17782236658', 230601);
insert into Customer
values(6, 'Aiden', 'male', '17782238080', 230729);
insert into Customer
values(7, 'Carl', 'male', '17782238083', 230730);
insert into Customer
values(8, 'Jinbae', 'male', '17782238058', 230801);

insert into Payment2
values(50, 10, 60);
insert into Payment2
values(75, 15, 90);
insert into Payment2
values(100, 20, 120);
insert into Payment2
values(35, 7, 42);
insert into Payment2
values(60, 12, 72);

insert into Payment1
values(10001, 'Card', 50, 10, 1, 1);
insert into Payment1
values(10002, 'Cash', 75, 15, 2, 2);
insert into Payment1
values(10003, 'Card', 100, 20, 3, 3);
insert into Payment1
values(10004, 'Cash', 35, 7, 4, 4);
insert into Payment1
values(10005, 'Card', 60, 12, 5, 5);

insert into Feedback2
values(10, 'T');
insert into Feedback2
values(8, 'T');
insert into Feedback2
values(5, 'F');
insert into Feedback2
values(3, 'F');
insert into Feedback2
values(2, 'F');
insert into Feedback2
values(1, 'F');

insert into Feedback1
values('FDB01', 10, 1, 1);
insert into Feedback1
values('FDB02', 8, 2, 2);
insert into Feedback1
values('FDB03', 5, 3, 3);
insert into Feedback1
values('FDB04', 3, 4, 4);
insert into Feedback1
values('FDB05', 2, 5, 5);
insert into Feedback1
values('FDB07', 1, 5, 2);

insert into GetServiced
values('STY001', 1);
insert into GetServiced
values('STY004', 4);
insert into GetServiced
values('CUT005', 5);

insert into Appointment
values(1001, 230728, 1400, 1, 1);
insert into Appointment
values(1002, 230729, 1000, 2, 2);
insert into Appointment
values(1003, 230729, 1300, 3, 3);
insert into Appointment
values(1004, 230729, 1600, 4, 4);
insert into Appointment
values(1005, 230730, 1100, 5, 5);

/*
simple tests
*/

SELECT *
FROM HairSalon;
SELECT *
FROM HasMany_Stylist2;
SELECT *
FROM HasMany_Stylist1;
SELECT *
FROM Assist_StylistAssistant;
SELECT *
FROM Service;
SELECT *
FROM Styling;
SELECT *
FROM Haircut;
SELECT *
FROM Coloring;
SELECT *
FROM Has_Receptionist;
SELECT *
FROM Appointment;
SELECT *
FROM Payment2;
SELECT *
FROM Payment1;
SELECT *
FROM Feedback2;
SELECT *
FROM Feedback1;
SELECT *
FROM Customer;
SELECT *
FROM GetServiced;
