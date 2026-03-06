<div class="modal show rematch-invitation-modal">
    <div class="modal-content">
        <h2>Invitation à une nouvelle partie</h2>
        <div class="invitation-content">
            <p class="invitation-text">
                <strong><?= htmlspecialchars($invitation['host_username']) ?></strong> 
                vous propose une revanche !
            </p>
            <p class="invitation-details">
                Acceptez-vous de jouer une nouvelle partie ?
            </p>
        </div>
        <div class="modal-actions">
            <form method="POST" action="<?= BASE_URL ?>/accepter-rematch" style="display: inline;">
                <input type="hidden" name="code" value="<?= $invitation['code'] ?>">
                <button type="submit" class="btn btn-accept">
                    ✓ Accepter
                </button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/refuser-rematch" style="display: inline;">
                <input type="hidden" name="code" value="<?= $invitation['code'] ?>">
                <button type="submit" class="btn btn-decline">
                    ✕ Refuser
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.rematch-invitation-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease-in;
    left: 50%;
    width: 100%;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.rematch-invitation-modal .modal-content {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 25px;
    padding: 2.5rem;
    width: 90%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 3px #667eea;
    animation: slideDown 0.4s ease-out;
    position: relative;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.rematch-invitation-modal h2 {
    margin: 0 0 1.5rem 0;
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Zone de contenu */
.invitation-content {
    margin: 2rem 0;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border-radius: 15px;
    border: 2px solid rgba(102, 126, 234, 0.1);
}

.invitation-text {
    font-size: 1.2rem;
    color: #333;
    margin: 0.5rem 0;
    font-weight: 600;
}

.invitation-details {
    font-size: 1rem;
    color: #667;
    margin: 1rem 0 0 0;
}

/* Boutons */
.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    justify-content: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    flex: 1;
}

.btn-accept {
    background: #667eea;
    color: white;
}

.btn-accept:hover {
    background: #764ba2;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-decline {
    background: white;
    color: #e74c3c;
    border: 2px solid #e74c3c;
}

.btn-decline:hover {
    background: #e74c3c;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}
</style>