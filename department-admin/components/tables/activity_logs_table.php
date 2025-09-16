<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-clipboard-text-clock"></i></span>
            Room Usage Logs
        </p>
    </header>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="card-content">
        <!-- Activity Logs Table -->
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div style="display: flex; align-items: center;">
                    <label style="margin-right: 10px;">Show</label>
                    <select id="entriesSelect" class="form-select" style="width: 80px; margin-right: 10px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label>entries</label>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div>
                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Usage Status</label>
                        <select id="usage-filter" class="form-select" style="width: 150px;">
                            <option value="">All Usage</option>
                            <option value="upcoming" <?php echo isset($_GET['usage']) && $_GET['usage'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="active" <?php echo isset($_GET['usage']) && $_GET['usage'] === 'active' ? 'selected' : ''; ?>>Currently Active</option>
                            <option value="completed" <?php echo isset($_GET['usage']) && $_GET['usage'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>                  
                    <!-- Search bar -->
                    <div>
                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Search</label>
                        <input type="text" id="customSearch" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;" placeholder="Search by user, room, or activity...">
                    </div>
                </div>
            </div>
            <table id="activityTable" class="table is-fullwidth is-striped">
                <thead>
                    <tr class="titles">
                        <th>User</th>
                        <th>Room</th>
                        <th>Activity</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            // Determine activity status with current date
                            $now = new DateTime(); // Use actual current date
                            $currentDate = new DateTime(date('Y-m-d')); // Just the date part of today
                            $reservationDate = new DateTime($row['ReservationDate']); // The reservation date
                            
                            $status = $row['Status'];
                            // Check explicitly if the reservation date is in the future, today, or past
                            if ($reservationDate > $currentDate) {
                                $statusClass = "status-upcoming";
                                $statusLabel = "Upcoming";
                            } elseif ($reservationDate < $currentDate) {
                                $statusClass = "status-completed";
                                $statusLabel = "Completed";
                            } else {
                                // Reservation is today - check the time
                                $currentTimeStr = date('H:i:s');
                                $startTimeStr = date('H:i:s', strtotime($row['StartTime']));
                                $endTimeStr = date('H:i:s', strtotime($row['EndTime']));
                                
                                if ($currentTimeStr < $startTimeStr) {
                                    $statusClass = "status-upcoming";
                                    $statusLabel = "Later Today";
                                } elseif ($currentTimeStr > $endTimeStr) {
                                    $statusClass = "status-completed";
                                    $statusLabel = "Completed Today";
                                } else {
                                    $statusClass = "status-active";
                                    $statusLabel = "Active Now";
                                }
                            }
                            
                            // Get user initials for avatar
                            $nameParts = explode(' ', $row['user_name']);
                            $initials = '';
                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($row['user_name'], 0, 2));
                            }
                        ?>
                            <tr>
                                <td data-label="User">
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo $initials; ?></div>
                                        <div class="user-details">
                                            <span class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></span>
                                            <span class="user-role"><?php echo $row['user_role']; ?> (<?php echo htmlspecialchars($row['user_department']); ?>)</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Room"><?php echo htmlspecialchars($row['room_name'] . ' (' . $row['building_name'] . ')'); ?></td>
                                <td data-label="Activity"><?php echo htmlspecialchars($row['ActivityName']); ?></td>
                                <td data-label="Date & Time">
                                    <?php 
                                        // Display the reservation day from ReservationDate (DATE)
                                        echo date('M j, Y', strtotime($row['ReservationDate'])); 
                                        echo '<br><span style="font-size: 0.8rem; color: #64748b;">';
                                        echo date('g:i A', strtotime($row['StartTime'])) . ' - ' . date('g:i A', strtotime($row['EndTime']));
                                        echo '</span>';
                                    ?>
                                </td>
                                <td data-label="Status">
                                    <span class="activity-status <?php echo $statusClass; ?>">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td data-label="Last Updated">
                                    <?php 
                                        echo time_elapsed_string($row['RequestDate']);
                                        if ($status == 'approved' && !empty($row['admin_first_name'])) {
                                            echo '<br><span style="font-size: 0.8rem; color: #64748b;">by ' . 
                                                htmlspecialchars($row['admin_first_name'] . ' ' . $row['admin_last_name']) . '</span>';
                                        }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="dept_room_approval.php?view=<?php echo $row['RequestID']; ?>" class="view-btn" title="View Details">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">No activity logs found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>