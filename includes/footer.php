    </main>
    <footer class="text-center py-3 text-muted small border-top bg-white">
        &copy; <?= date('Y') ?> MyFruit Official &mdash; Sistem POS
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const btnToggle = document.getElementById('btnToggleSidebar');
    if (btnToggle) {
        btnToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
    }
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }
</script>
</body>
</html>
