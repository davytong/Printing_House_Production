<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 419 - Page Expired</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Hanuman', 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .error-card {
            background: #fff;
            padding: 2.8rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .error-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            margin-bottom: 1.5rem;
            background: #fef3c7;
            color: #d97706;
        }
        .btn-retry {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
            border-radius: 12px;
            padding: .65rem 1.4rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: .2s ease;
        }
        .btn-retry:hover {
            background: #3730a3;
            border-color: #3730a3;
            color: #fff;
        }
        .btn-home {
            background: transparent;
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 12px;
            padding: .65rem 1.4rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: .2s ease;
        }
        .btn-home:hover {
            background: #f8fafc;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <h3 class="fw-bold mb-2">ទំព័របានផុតកំណត់ / Page Expired (419)</h3>
        <p class="text-secondary mb-4" style="font-size: .92rem; line-height: 1.6">
            សម័យកាលសុវត្ថិភាព (Security Session) បានផុតកំណត់ ឬទំហំឯកសារធំពេក។<br>
            <strong>ទិន្នន័យព្រាងរបស់អ្នកត្រូវបានចងចាំក្នុងទូរស័ព្ទ។</strong> សូមចុចត្រឡប់ក្រោយដើម្បីបន្ត។
            <br>
            <span class="text-muted d-block mt-2" style="font-size: .8rem">
                Your session timed out. Please go back to reload a fresh token and resubmit.
            </span>
        </p>
        <div class="d-flex flex-column gap-2">
            <button type="button" onclick="history.back()" class="btn-retry w-100">
                <i class="bi bi-arrow-clockwise"></i> ត្រឡប់ក្រោយ &amp; បញ្ជូនម្តងទៀត (Go Back &amp; Retry)
            </button>
            <a href="/" class="btn-home w-100">
                <i class="bi bi-house-door"></i> ត្រឡប់ទៅផ្ទាំងដើម (Dashboard)
            </a>
        </div>
    </div>
</body>
</html>
