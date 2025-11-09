<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ارسال ایمیل انبوه</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; }
        .card { max-width: 900px; margin: 20px auto; }
        .loader { display: none; text-align: center; padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">ارسال ایمیل انبوه</h4>
        </div>
        <div class="card-body">
            <form id="emailForm" action="send.php" method="POST" enctype="multipart/form-data">
                <!-- عنوان -->
                <div class="mb-3">
                    <label class="form-label">عنوان ایمیل</label>
                    <input type="text" name="subject" class="form-control" required placeholder="مثال: سیاسفید 6 - رویداد یلدایی">
                </div>

                <!-- ویرایشگر HTML -->
                <div class="mb-3">
                    <label class="form-label">محتوای HTML ایمیل</label>
                    <textarea name="html_body" id="htmlEditor"></textarea>
                </div>

                <!-- آپلود لیست ایمیل (JSON یا TXT) -->
                <div class="mb-3">
                    <label class="form-label">آپلود لیست ایمیل‌ها (JSON یا TXT - هر خط یک ایمیل)</label>
                    <input type="file" name="email_list" class="form-control" accept=".json,.txt">
                    <small class="text-muted">اگر فایلی آپلود نکنید، از <code>emails.json</code> استفاده می‌شود.</small>
                </div>

                <!-- متن جایگزین -->
                <div class="mb-3">
                    <label class="form-label">متن جایگزین (Plain Text)</label>
                    <textarea name="alt_body" class="form-control" rows="3" placeholder="برای کاربرانی که HTML نمی‌بینند..."></textarea>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100" id="sendBtn">
                    ارسال ایمیل به همه
                </button>
            </form>

            <div class="loader" id="loader">
                <div class="spinner-border text-primary"></div>
                <p>در حال ارسال... لطفاً منتظر بمانید</p>
            </div>

            <div id="result"></div>
        </div>
    </div>
</div>

<script>
    tinymce.init({
        selector: '#htmlEditor',
        height: 400,
        language: 'fa',
        directionality: 'rtl',
        plugins: 'lists link image table code',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        setup: function (editor) {
            // بارگذاری محتوای پیش‌فرض
            fetch('email_template.html')
                .then(r => r.text())
                .then(html => editor.setContent(html))
                .catch(() => editor.setContent('<p>محتوای ایمیل را اینجا بنویسید...</p>'));
        }
    });

    // ارسال فرم با AJAX
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sendBtn = document.getElementById('sendBtn');
        const loader = document.getElementById('loader');
        const result = document.getElementById('result');

        sendBtn.disabled = true;
        loader.style.display = 'block';
        result.innerHTML = '';

        fetch('send.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            result.innerHTML = html;
            loader.style.display = 'none';
            sendBtn.disabled = false;
        })
        .catch(err => {
            result.innerHTML = `<div class="alert alert-danger">خطا در ارتباط با سرور: ${err.message}</div>`;
            loader.style.display = 'none';
            sendBtn.disabled = false;
        });
    });
</script>

</body>
</html>
