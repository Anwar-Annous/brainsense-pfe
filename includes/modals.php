<!-- Notes Modal -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Notes for <span id="patientName"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="notesForm">
                <input type="hidden" id="patientId" name="patientId">
                <div class="form-group">
                    <label for="noteType">Note Type</label>
                    <select id="noteType" name="noteType" required>
                        <option value="consultation">Consultation</option>
                        <option value="observation">Observation</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="prescription">Prescription</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="noteContent">Notes</label>
                    <textarea id="noteContent" name="noteContent" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="notePriority">Priority</label>
                    <select id="notePriority" name="notePriority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Note</button>
                    <button type="button" class="btn-secondary" onclick="closeNotesModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Send Message to <span id="messagePatientName"></span></h3>
            <span class="close-message">&times;</span>
        </div>
        <div class="modal-body">
            <form id="messageForm">
                <input type="hidden" id="messagePatientId" name="patientId">
                <div class="form-group">
                    <label for="messageSubject">Subject</label>
                    <input type="text" id="messageSubject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="messageContent">Message</label>
                    <textarea id="messageContent" name="message" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="messagePriority">Priority</label>
                    <select id="messagePriority" name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Send Message</button>
                    <button type="button" class="btn-secondary" onclick="closeMessageModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Consultation Modal -->
<div id="consultationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New Consultation</h3>
            <span class="close-consultation">&times;</span>
        </div>
        <div class="modal-body">
            <form id="consultationForm">
                <div class="form-group">
                    <label for="patientSelect">Select Patient</label>
                    <select id="patientSelect" name="patient_id" required>
                        <option value="">Select a patient</option>
                        <?php
                        // Get all patients
                        $stmt = $conn->query("SELECT id, full_name FROM patients ORDER BY full_name");
                        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($patients as $patient) {
                            echo "<option value='" . $patient['id'] . "'>" . htmlspecialchars($patient['full_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="consultationType">Consultation Type</label>
                    <select id="consultationType" name="type" required>
                        <option value="En personne">In Person</option>
                        <option value="En ligne">Online</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="consultationDate">Date</label>
                    <input type="date" id="consultationDate" name="appointment_date" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="consultationTime">Time</label>
                    <input type="time" id="consultationTime" name="appointment_time" required>
                </div>
                <div class="form-group">
                    <label for="consultationPurpose">Purpose</label>
                    <textarea id="consultationPurpose" name="purpose" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="consultationLocation">Location</label>
                    <input type="text" id="consultationLocation" name="location" 
                           placeholder="Enter location or leave empty for online consultations">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Schedule Consultation</button>
                    <button type="button" class="btn-secondary" onclick="closeConsultationModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div id="historyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Patient History - <span id="historyPatientName"></span></h3>
            <span class="close-history">&times;</span>
        </div>
        <div class="modal-body">
            <div class="history-filters">
                <select id="historyTypeFilter">
                    <option value="all">All Appointments</option>
                    <option value="En personne">In Person</option>
                    <option value="En ligne">Online</option>
                </select>
                <select id="historyStatusFilter">
                    <option value="all">All Status</option>
                    <option value="Completed">Completed</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="history-list" id="historyList">
                <!-- History items will be loaded here -->
            </div>
        </div>
    </div>
</div> 