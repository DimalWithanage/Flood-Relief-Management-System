function showToast(message, type = 'info') {
    alert(message);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {

    
    // Login Page
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            const btn = document.getElementById('loginBtn');
            btn.textContent = 'Signing in...';
            btn.disabled = true;

            fetch('php/login.php', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email: email, password: password })
            })
                .then(response => response.text())
                .then(data => {
                    if (data.startsWith('Success:')) {
                        const role = data.split(':')[1].trim();
                        showToast('Login successful!', 'success');
                        setTimeout(() => {
                            if (role === 'admin') {
                                window.location.href = 'admin.html';
                            } else {
                                window.location.href = 'dashboard.html';
                            }
                        }, 1000);
                    } else {
                        showToast(data, 'error');
                        btn.textContent = 'Sign In';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    showToast('Something went wrong. Please try again.', 'error');
                    btn.textContent = 'Sign In';
                    btn.disabled = false;
                });
        });
    }

    // Register Page
    
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const full_name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;
            const phone = document.getElementById('regPhone').value;
            const address = document.getElementById('regAddress').value;

            const btn = document.getElementById('registerBtn');
            btn.textContent = 'Registering...';
            btn.disabled = true;

            fetch('php/register.php', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    full_name: full_name,
                    email: email,
                    password: password,
                    phone: phone,
                    address: address
                })
            })
                .then(response => response.text())
                .then(data => {
                    if (data.startsWith('Success:')) {
                        showToast(data.substring(8).trim(), 'success');
                        setTimeout(() => {
                            window.location.href = 'index.html';
                        }, 1500);
                    } else {
                        showToast(data, 'error');
                        btn.textContent = 'Register';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    showToast('Something went wrong. Please try again.', 'error');
                    btn.textContent = 'Register';
                    btn.disabled = false;
                });
        });
    }
    
    // Dashboard Page
    const createRequestForm = document.getElementById('createRequestForm');
    if (createRequestForm) {
        loadUserRequests();

        createRequestForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = document.getElementById('createBtn');
            btn.textContent = 'Submitting...';
            btn.disabled = true;

            const dataObj = Object.fromEntries(formData.entries());

            fetch('php/create_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            })
                .then(res => res.text())
                .then(data => {
                    if (data.startsWith('Success:')) {
                        showToast(data.substring(8).trim(), 'success');
                        document.getElementById('createRequestForm').reset();
                        loadUserRequests();
                    } else {
                        showToast(data, 'error');
                    }
                    btn.textContent = 'Submit Request';
                    btn.disabled = false;
                })
                .catch(() => {
                    showToast('Something went wrong.', 'error');
                    btn.textContent = 'Submit Request';
                    btn.disabled = false;
                });
        });
        
        document.getElementById('editRequestForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            const dataObj = Object.fromEntries(formData.entries());

            fetch('php/update_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            })
                .then(res => res.text())
                .then(data => {
                    if (data.startsWith('Success:')) {
                        showToast(data.substring(8).trim(), 'success');
                        closeModal('editModal');
                        loadUserRequests();
                    } else {
                        showToast(data, 'error');
                    }
                })
                .catch(() => showToast('Something went wrong.', 'error'));
        });
    }

    // Admin Dashboard Page
    if (document.getElementById('usersTableBody')) {
        loadUsers();
        loadReports();
    }
});

// Global Functions from Dashboard

function loadUserRequests() {
    fetch('php/get_requests.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('requestsTableBody');
            if (!data.success || !data.data || data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="icon">📋</div><p>No relief requests yet. Create your first request!</p></div></td></tr>';
                return;
            }
            tbody.innerHTML = data.data.map((r, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><span class="badge badge-${r.relief_type.toLowerCase()}">${r.relief_type}</span></td>
                    <td>${r.district}</td>
                    <td><span class="badge badge-${r.severity.toLowerCase()}">${r.severity}</span></td>
                    <td>${r.family_members}</td>
                    <td><span class="badge badge-${r.status.toLowerCase()}">${r.status}</span></td>
                    <td>${new Date(r.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="editRequest(${r.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRequest(${r.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(() => {
            const tbody = document.getElementById('requestsTableBody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center">Failed to load requests.</td></tr>';
        });
}

function editRequest(id) {
    fetch('php/get_requests.php')
        .then(res => res.json())
        .then(data => {
            const request = data.data.find(r => parseInt(r.id) === id);
            if (!request) return showToast('Request not found.', 'error');

            document.getElementById('editId').value = request.id;
            document.getElementById('editReliefType').value = request.relief_type;
            document.getElementById('editSeverity').value = request.severity;
            document.getElementById('editDistrict').value = request.district;
            document.getElementById('editDivSecretariat').value = request.divisional_secretariat;
            document.getElementById('editGnDivision').value = request.gn_division;
            document.getElementById('editContactPerson').value = request.contact_person;
            document.getElementById('editContactNumber').value = request.contact_number;
            document.getElementById('editAddress').value = request.address;
            document.getElementById('editFamilyMembers').value = request.family_members;
            document.getElementById('editDescription').value = request.description || '';
            openModal('editModal');
        });
}

function deleteRequest(id) {
    document.getElementById('deleteRequestId').value = id;
    openModal('deleteModal');
}

function confirmDeleteRequest() {
    const id = document.getElementById('deleteRequestId').value;
    const formData = new FormData();
    formData.append('id', id);

    const dataObj = Object.fromEntries(formData.entries());

    fetch('php/delete_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataObj)
    })
        .then(res => res.text())
        .then(data => {
            if (data.startsWith('Success:')) {
                showToast(data.substring(8).trim(), 'success');
                closeModal('deleteModal');
                loadUserRequests();
            } else {
                showToast(data, 'error');
            }
        })
        .catch(() => showToast('Something went wrong.', 'error'));
}

function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
    if (event && event.target) {
        event.target.classList.add('active');
    }
    if (tab === 'requests') loadUserRequests();
}
