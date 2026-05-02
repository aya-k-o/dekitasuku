// 削除確認ダイアログ
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.btn-delete');
    deleteForms.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('本当に削除しますか？')) {
                e.preventDefault();
            }
        });
    });
});

// できた！ボタンの連打防止
document.addEventListener('DOMContentLoaded', function() {
    const doneForms = document.querySelectorAll('form[action="complete.php"]');
    doneForms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('.btn-done');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'やったね！';
            }
        });
    });
});

// 顔アイコンの選択
document.addEventListener('DOMContentLoaded', function() {
    const faceSelects = document.querySelectorAll('.face-select');
    faceSelects.forEach(function(group) {
        const name = group.dataset.name;
        const hiddenInput = document.getElementById(name);
        const faceBtns = group.querySelectorAll('.face-btn');

        faceBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                faceBtns.forEach(function(b) {
                    b.classList.remove('selected');
                });
                btn.classList.add('selected');
                hiddenInput.value = btn.dataset.value;
            });
        });
    });
});