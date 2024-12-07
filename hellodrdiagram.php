<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelloDr Directory Structure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 20px;
        }
        .directory {
            margin-left: 20px;
        }
        .folder, .file {
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .folder::before, .file::before {
            content: "";
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }
        .folder::before {
            content: "📂";
        }
        .file::before {
            content: "📄";
        }
        .hidden {
            display: none;
        }
        ul {
            list-style: none;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1>HelloDr Directory Structure</h1>
    <div id="directory-structure">
        <ul>
            <li>
                <span class="folder">HelloDr/</span>
                <ul class="directory">
                    <li>
                        <span class="folder">admin/</span>
                        <ul class="directory hidden">
                            <li>
                                <span class="folder">css/</span>
                                <ul class="directory hidden">
                                    <li><span class="file">admin_login.css</span></li>
                                    <li><span class="file">admin_signup.css</span></li>
                                    <li><span class="file">manage_doctors.css</span></li>
                                    <li><span class="file">manage_patients.css</span></li>
                                </ul>
                            </li>
                            <li><span class="folder">images/</span></li>
                            <li><span class="file">admin_dashboard.php</span></li>
                            <li><span class="file">admin_login.php</span></li>
                            <li><span class="file">admin_signup.php</span></li>
                            <li><span class="file">manage_doctors.php</span></li>
                            <li><span class="file">manage_patients.php</span></li>
                            <li><span class="file">team.php</span></li>
                        </ul>
                    </li>
                    <li>
                        <span class="folder">css/</span>
                        <ul class="directory hidden">
                            <li><span class="file">about.css</span></li>
                            <li><span class="file">appointment.css</span></li>
                            <li><span class="file">contact.css</span></li>
                            <li><span class="file">doctor lists.css</span></li>
                            <li><span class="file">index.css</span></li>
                            <li><span class="file">login.css</span></li>
                            <li><span class="file">send_message.css</span></li>
                            <li><span class="file">send_message_action.css</span></li>
                            <li><span class="file">styles.css</span></li>
                        </ul>
                    </li>
                    <li>
                        <span class="folder">doctor info/</span>
                        <ul class="directory hidden">
                            <li>
                                <span class="folder">css/</span>
                                <ul class="directory hidden">
                                    <li><span class="file">doctor_dashboard.css</span></li>
                                    <li><span class="file">nav.css</span></li>
                                    <li><span class="file">profile-card.css</span></li>
                                    <li><span class="file">view_appointments.css</span></li>
                                    <li><span class="file">view_profile.css</span></li>
                                </ul>
                            </li>
                            <li><span class="folder">images/</span></li>
                            <li><span class="file">approve_appointment.php</span></li>
                            <li><span class="file">cancel_appointment.php</span></li>
                            <li><span class="file">doctor_conversation.php</span></li>
                            <li><span class="file">doctor_dashboard.php</span></li>
                            <li><span class="file">doctor_logout.php</span></li>
                            <li><span class="file">doctor_profile.php</span></li>
                            <li><span class="file">doctor_profile_edit.php</span></li>
                            <li><span class="file">doctor_signup.php</span></li>
                            <li><span class="file">fetch_messages.php</span></li>
                            <li><span class="file">profile-card.php</span></li>
                            <li><span class="file">send_message.php</span></li>
                            <li><span class="file">update_appointment.php</span></li>
                            <li><span class="file">update_profile.php</span></li>
                            <li><span class="file">view_appointments.php</span></li>
                            <li><span class="file">view_profile.php</span></li>
                        </ul>
                    </li>
                    <li>
                        <span class="folder">user_info/</span>
                        <ul class="directory hidden">
                            <li>
                                <span class="folder">css/</span>
                                <ul class="directory hidden">
                                    <li><span class="file">user_login.css</span></li>
                                    <li><span class="file">user_signup.css</span></li>
                                </ul>
                            </li>
                            <li><span class="folder">images/</span></li>
                            <li><span class="file">edit_user_profile.php</span></li>
                            <li><span class="file">user_login.php</span></li>
                            <li><span class="file">user_profile.php</span></li>
                            <li><span class="file">user_signup.php</span></li>
                        </ul>
                    </li>
                    <li>
                        <span class="folder">images/</span>
                    </li>
                    <li><span class="file">main.js</span></li>
                    <li><span class="file">about.php</span></li>
                    <li><span class="file">add_review.php</span></li>
                    <li><span class="file">appointment.php</span></li>
                    <li><span class="file">appointment_success.php</span></li>
                    <li><span class="file">config.php</span></li>
                    <li><span class="file">contact.php</span></li>
                    <li><span class="file">conversation.php</span></li>
                    <li><span class="file">doctor lists.php</span></li>
                    <li><span class="file">footer.php</span></li>
                    <li><span class="file">get_new_messages.php</span></li>
                    <li><span class="file">header.php</span></li>
                    <li><span class="file">index.php</span></li>
                    <li><span class="file">login.php</span></li>
                    <li><span class="file">logout.php</span></li>
                    <li><span class="file">request_appointment.php</span></li>
                    <li><span class="file">request_appointment_action.php</span></li>
                    <li><span class="file">send_message.php</span></li>
                    <li><span class="file">send_message_action.php</span></li>
                    <li><span class="file">setup.php</span></li>
                    <li><span class="file">signup.php</span></li>
                </ul>
            </li>
        </ul>
    </div>
    <script>
        document.querySelectorAll(".folder").forEach(folder => {
            folder.addEventListener("click", () => {
                const directory = folder.nextElementSibling;
                if (directory) {
                    directory.classList.toggle("hidden");
                }
            });
        });
    </script>
</body>
</html>
