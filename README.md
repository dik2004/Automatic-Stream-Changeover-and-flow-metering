# Automatic Stream Changeover - Flow Metering System

A modern web-based system for monitoring and automatically managing flow metering streams with real-time data visualization and control capabilities.

## Features

- Real-time monitoring of flow rates, pressure, and temperature
- Automatic stream changeover based on configurable thresholds
- Manual override capability
- Responsive and modern user interface
- Data persistence with MySQL database
- RESTful API for data management

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone [repository-url]
   ```

2. Create a MySQL database and import the database structure:
   ```bash
   mysql -u root -p < database/setup.sql
   ```

3. Configure the database connection in `api/stream_data.php`:
   ```php
   $db_config = [
       'host' => 'localhost',
       'dbname' => 'flow_metering',
       'username' => 'your_username',
       'password' => 'your_password'
   ];
   ```

4. Ensure proper file permissions:
   ```bash
   chmod 755 -R .
   chmod 777 -R api/
   ```

## Usage

1. Access the system through your web browser:
   ```
   http://localhost/project
   ```

2. The dashboard will show:
   - Current stream status
   - Real-time flow rate, pressure, and temperature
   - Stream control panel
   - System settings

3. Configure thresholds in the Settings section:
   - Changeover threshold (m³/h)
   - Pressure limit (bar)
   - Temperature limit (°C)

4. Use the Manual Override button to take control of stream switching
5. Enable Auto Mode for automatic stream management

## API Endpoints

### GET /api/stream_data.php
Retrieves current stream data and measurements.

### POST /api/stream_data.php
Updates stream status and records new measurements.

## Directory Structure

```
project/
├── api/
│   └── stream_data.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── database/
│   └── setup.sql
├── images/
└── index.php
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the repository or contact the system administrator. 