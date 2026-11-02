# Medical Appointment Management API

A RESTful API built with Laravel for managing medical appointments between doctors and patients. The system supports appointment scheduling, doctor and patient profile management, availability management, medical history tracking, and administrative tools.

## Table of Contents

- [Overview](#overview)
- [ERD](#ERD)
- [User Roles](#user-roles)
- [Core Features](#core-features)
- [Scheduling Logic](#scheduling-logic)
- [Technology Stack](#technology-stack)

## Overview

This API provides a complete backend for managing medical appointments. It connects doctors and patients through a structured scheduling system, with role-based access control ensuring each user type can only perform actions relevant to their responsibilities.

## ERD
![Entity Relationship Diagram](https://github.com/GH0STYtopflo/Reservation_System/blob/main/composer.json/ERD.png)

## User Roles

- **Admin**: Manages users, doctors, patients, cities, specialties, appointments, and system analytics.
- **Doctor**: Manages their profile, specialties, availability, unavailability, and appointments.
- **Patient**: Manages their profile, medical history, and appointments.

## Core Features

- **Authentication and authorization**: Role-based access control for admins, doctors, and patients.
- **Profile management**: Doctor and patient profiles with full CRUD operations.
- **City and specialty management**: Administrative control over the locations and medical specialties offered.
- **Availability scheduling**: Support for both recurring and one-time doctor availabilities.
- **Unavailability management**: Doctors can block out periods when they cannot accept appointments.
- **Public slot discovery**: Anyone can query available appointment slots for a doctor.
- **Appointment lifecycle**: Booking, approval, rescheduling, cancellation, and completion.
- **Medical history**: Patients can maintain their medical history records.
- **Analytics**: Administrative insights into system activity.

## Scheduling Logic

Scheduling is driven by two concepts:

- **Availabilities** define a doctor's working hours, either as recurring weekly patterns or one-time windows.
- **Unavailabilities** define periods when a doctor cannot accept appointments, such as vacations or personal leave.

Available slots are calculated by combining these schedules and subtracting existing appointments. Any booking that would overlap with an existing appointment is rejected, preventing double-booking.

## Technology Stack

- **Language**: PHP 8.4
- **Framework**: Laravel
- **Database**: PostgreSQL
- **Containerization**: Docker and Docker Compose
- **Code quality**: Pint (code style) and Rector (automated refactoring)
- **Recurrence rules**: [rlanvin/php-rrule](https://github.com/rlanvin/php-rrule)

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Git

```bash
git clone https://github.com/GH0STYtopflo/Reservation_System.git
cd Reservation_System
cp .env.example .env
docker compose up --build
