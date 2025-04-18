            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                    <label for="employeeType" class="form-label required">Employee Type</label>
                        <select class="form-select" id="employeeType" name="employeeType" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Conductor">Conductor</option>
                            <option value="Driver">Driver</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="employeeNumber" class="form-label required">Employee No.</label>
                        <input type="text" class="form-control" id="employeeNumber" name="employeeNumber" placeholder="Auto generated" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="firstName" class="form-label required">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middleName" class="form-label required">Middle Name</label>
                        <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle name">
                    </div>
                    <div class="col-md-4">
                        <label for="lastName" class="form-label required">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter last name" required>
                    </div>
                </div>

                <div class="row mb-3">
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label required">Phone</label>
                        <div class="form-group position-relative">
                            <input type="text" class="form-control ps-5" id="phone" name="contactnumber" placeholder="" required pattern="\d{10}" maxlength="10" />
                            <span class="position-absolute top-50 start-0 translate-middle-y ps-2 text-muted">+63</span>
                        </div>
                        <div id="contactError" class="invalid-feedback" style="display: none;"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    
                <div class="col-md-6">
                        <label for="dob" class="form-label required">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
                <div class="col-md-6">   
                        <label for="gender" class="form-label required">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                </div>
                </div>
                <div class="row mb-3">   
                    <div class="col-md-12">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Purok#/Street/Sitio"> 
                    </div>
                </div>
                <div class="row mb-3">         
                    <div class="col-md-4">
                        <label for="province" class="form-label">Province</label>
                        <select class="form-select" id="province" name="province">
                            <option value="">-- Select Province --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="municipality" class="form-label">Municipality</label>
                        <select class="form-select" id="municipality" name="municipality">
                            <option value="">-- Select Municipality --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="barangay" class="form-label">Barangay</label>
                        <select class="form-select" id="barangay" name="barangay">
                            <option value="">-- Select Barangay --</option>
                        </select>
                    </div>
                </div>

                <!-- Conditional fields for Driver -->
                <div id="driverFields" class="driver-fields" style="display:none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                                <label for="driverLicense" class="form-label required">Driver's License No.</label>
                                <input type="text" class="form-control" id="driverLicense" name="driverLicense" placeholder="Enter license number">
                        </div>
                    </div>
                </div>

                <!-- Conditional fields for Conductor -->
                <div id="conductorFields" class="conductor-fields" style="display:none;">
                    <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="workExperience" class="form-label required">Work Experience</label>
                            <textarea class="form-control" id="workExperience" rows="2" placeholder="Enter work experience"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Conditional fields for Cashier -->
                <div id="cashierFields" class="cashier-fields" style="display:none;">
                    <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="cashHandlingExperience" class="form-label required">Cash Handling Experience</label>
                            <textarea class="form-control" id="cashHandlingExperience" rows="2" placeholder="Enter experience in cash handling"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>