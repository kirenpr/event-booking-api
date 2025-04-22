# 🗓️ Event Booking API

A RESTful API built using **Symfony 7.2** for managing events, attendees, and bookings.

---

## ✅ 1. Project Approach

This Event Booking API is designed with **separation of concerns** using a layered architecture:

- **Entities**: `Event`, `Attendee`, and `Booking`, with proper relationships and constraints.
- **Validation**: All incoming requests are validated using Symfony’s Validator component.
- **Business Logic**: Prevents overbooking and duplicate bookings via service layer.
- **Testing**: Includes unit tests for key endpoints in `AttendeeController` and `BookingController`.

---

## ⚙️ 2. Setup Instructions

### Prerequisites

- PHP 8.2
- Composer
- Symfony CLI
- MySQL

### 🔧 Steps to Set Up

1. **Download the codebase and unzip the project folder.**

2. **cd to project folder and install dependencies by running from terminal.**
   composer install

3. **Create a .env file and copy the contents of .env.dev into .env file**

4. **Run the project on a local server**
   symfony server:start

5. **Create database and schema**

   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate

6. **Run tests**

   php bin/phpunit

---

## 🤝 3. Assumptions

- **Authentication/Authorization**:
- Not implemented in code.
- Event management (create/update/delete) is assumed to be restricted to authenticated users.
- Attendee registration and booking are public.

- **Location**:
- Simplified to `country` only — no address/geolocation.

- **Booking Rules**:
- An attendee **cannot** book the same event more than once.
- Booking is allowed **only** if the event has available capacity.

---

## 🧱 4. Architecture Diagram

+-----------------------------+
| Client (UI) |
+-------------+--------------+
|
v
+-----------------------------+
| Symfony API (v7.2) |
+-----------------------------+
| Controllers: |
| - EventController |
| - AttendeeController |
| - BookingController |
+-----------------------------+
| Services: |
| - BookingService |
| - Validation logic |
+-----------------------------+
| Doctrine ORM + Repositories|
| Entities: Event, Attendee, |
| Booking |
+-----------------------------+
| MySQL Database |
+-----------------------------|

---

---

## 📬 5. Postman API Documentation

The API is fully documented via a Postman collection:

### 📄 File: `postman-docs.json`

### 📦 How to Use in Postman:

1. Open Postman.
2. Create or open a workspace.
3. Click **Import** → choose `postman-docs.json`.
4. Create an environment with:
   - **Key**: `base_url`
   - **Value**: `http://localhost:8000` (or your dev server URL)
5. Select the environment and start testing the API.

---

## 🚀 You're all set!

This project demonstrates a clean, testable, and scalable approach to building APIs with Symfony.
