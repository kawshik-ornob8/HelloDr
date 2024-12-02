<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
        }
        .sidebar {
            background-color: #2a2e49;
            color: white;
            height: 100vh;
            padding: 20px;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #475072;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: white;
            border-bottom: 1px solid #ddd;
        }
        .stats-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
            padding: 20px;
        }
        .chart-container {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
        }
        .appointments-table,
        .next-patient-details {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <h3>Dr. Marttin Deo</h3>
            <a href="#"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#"><i class="fas fa-calendar-check"></i> Appointment</a>
            <a href="#"><i class="fas fa-user"></i> Profile</a>
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <!-- Header -->
            <div class="dashboard-header">
                <div>
                    <h2>Dashboard</h2>
                    <p>Welcome back, Dr. Marttin Deo!</p>
                </div>
                <div>
                    <input type="text" class="form-control" placeholder="Search...">
                    <i class="fas fa-bell ms-3"></i>
                    <i class="fas fa-envelope ms-3"></i>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="stats-card bg-info text-white">
                        <h5>Total Patients</h5>
                        <h3>2000+</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-warning text-white">
                        <h5>Today's Patients</h5>
                        <h3>68</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-success text-white">
                        <h5>Today's Appointments</h5>
                        <h3>85</h3>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="row mt-4">
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5>Patients Summary</h5>
                        <canvas id="patientsChart"></canvas>
                    </div>
                </div>

                <!-- Next Patient Details -->
                <div class="col-md-6">
                    <div class="next-patient-details">
                        <h5>Next Patient Details</h5>
                        <p><strong>Name:</strong> Sanath Deo</p>
                        <p><strong>ID:</strong> 0220092020005</p>
                        <p><strong>Diagnosis:</strong> Asthma</p>
                        <button class="btn btn-primary">View History</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sample Chart
    const ctx = document.getElementById('patientsChart').getContext('2d');
    const patientsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['New Patients', 'Old Patients'],
            datasets: [{
                label: '# of Patients',
                data: [40, 60],
                backgroundColor: ['#ff6384', '#36a2eb']
            }]
        }
    });
</script>
</body>
</html>
