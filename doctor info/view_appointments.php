<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="css/view_appointments.css">
    <style>
        /* Notification styles */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            z-index: 9999;
            cursor: pointer;
            margin-top: 10px;
        }

        .notification a {
            color: white;
            text-decoration: underline;
        }

        #notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column-reverse;
        }
    </style>
</head>
<body>

<h2>Scheduled Appointments</h2>

<div class="container">
    <table id="appointments-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Patient Name</th>
                <th>Mobile Number</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Appointments will be dynamically inserted here -->
        </tbody>
    </table>

    <a href="doctor_dashboard.php" class="button">Back to Dashboard</a>
</div>

<script>
async function fetchAppointmentsAndCalls() {
    try {
        const response = await fetch('fetch_appointments_data.php');
        const data = await response.json();
        
        if (data.error) {
            console.error(data.error);
            return;
        }

        // Update appointments table
        const appointmentsTableBody = document.querySelector('#appointments-table tbody');
        appointmentsTableBody.innerHTML = ''; // Clear current rows
        data.appointments.forEach(appointment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${appointment.appointment_date}</td>
                <td>${appointment.appointment_time}</td>
                <td>${appointment.full_name}</td>
                <td>${appointment.mobile_number}</td>
                <td>${appointment.email}</td>
                <td>${appointment.status}</td>
                <td>
                    ${appointment.status === 'Pending' ? `
                        <form action="approve_appointment.php" method="post" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="${appointment.appointment_id}">
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                        <form action="cancel_appointment.php" method="post" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="${appointment.appointment_id}">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    ` : ''}
                </td>
            `;
            appointmentsTableBody.appendChild(row);
        });

        // Show pop-up notifications for active calls
        const notificationContainer = document.getElementById('notification-container');
        data.active_calls.forEach(call => {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.dataset.roomId = call.room_id;
            notification.innerHTML = `
                <p><strong>Active call with ${call.patient_name}</strong></p>
                <a href="dr_video_call_room.php?room_id=${encodeURIComponent(call.room_id)}">Join</a>
            `;
            notificationContainer.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 10000); // Auto-remove after 10 seconds
        });

    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

// Fetch data on page load and periodically
fetchAppointmentsAndCalls();
setInterval(fetchAppointmentsAndCalls, 10000); // Update every 10 seconds
</script>

<div id="notification-container"></div>

</body>
</html>
