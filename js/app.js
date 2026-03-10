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
