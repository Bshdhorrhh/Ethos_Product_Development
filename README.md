# UDBHAV_PROJECT

CampusSight: Campus Entity Resolution & Security Monitoring System

Description:
CampusSight is an intelligent, privacy-conscious platform designed to unify fragmented campus data into actionable insights. It links disparate data sources—access logs, Wi-Fi connections, library checkouts, CCTV footage—to create a cohesive “digital twin” of every campus entity. The system empowers security and operations teams to reconstruct timelines, predict probable entity locations, and receive intelligent alerts, all through a user-friendly dashboard.


Key Features:

Unified Data View: Integrates structured logs, unstructured text, and visual data.
Story View Timeline: Chronological visualization of entity activities across multiple systems.
Predictive Intelligence: Explainable AI predicts probable entity states with human-readable reasoning.
Intelligent Alerts: Real-time notifications for unusual events, anomalies, or inactivity.
Privacy-First: Role-based access and PII masking ensure ethical and secure handling of data.
System Architecture:
Database: MySQL backend with normalized, indexed tables; central entity resolution mapping table.
Predictive Model: Time-aware decision tree classifier for explainable and actionable predictions.
Dashboard: Interactive UI designed for security and operations personnel for fast situational awareness.

Setup Notes on Large Files:
Two large files required for running the project are hosted on Google Drive:

trained_location_model.joblib – Trained machine learning model
Download Link : https://drive.google.com/file/d/1tNCVH7JfrdXBcR89uWjm244h-Ingto6i/view?usp=sharing

face_images/ – Folder containing required face images
Download Link : https://drive.google.com/drive/folders/169wiGdcp1qIcAaFGGRCuX16niTgFjPaE?usp=sharing

Instructions:

Download the files from the above links.
Place trained_location_model.joblib in the project root directory.
Place the face_images folder in the project root/data directory along with csv files.
Create database in xampp phpMyAdmin with name "campus_entity_system"
Run command to setup database
```php setup_database.php```

Run command to import data from csv files to mysql database
```php import_data.php```
Run the project on ```http://localhost/your_directory/index.php```.

Conclusion:
CampusSight transforms reactive campus security into proactive intelligence, combining predictive monitoring, timeline reconstruction, and alerting, all while maintaining strong privacy and ethical standards.




