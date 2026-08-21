<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Hanuman', 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .error-card {
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.05);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: #4f46e5;
            border-color: #4f46e5;
            border-radius: 12px;
            padding: .6rem 1.5rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #3730a3;
            border-color: #3730a3;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon bg-danger-subtle text-danger">
            <i class="bi bi-compass"></i>
        </div>
        <h3 class="fw-bold mb-2">រកមិនឃើញទំព័រ / Page Not Found</h3>
        <p class="text-secondary mb-4" style="font-size: .9rem">
            ទំព័រដែលអ្នកកំពុងស្វែងរកមិនមាន ឬត្រូវបានផ្លាស់ប្តូរទៅកាន់ទីតាំងផ្សេងទៀត។
            <br>
            <span class="text-muted" style="font-size: .8rem">The page you are looking for does not exist or has been moved.</span>
        </p>
        <a href="/" class="btn btn-primary"><i class="bi bi-house-door me-2"></i>ត្រឡប់ទៅផ្ទាំងគ្រប់គ្រង / Back to Dashboard</a>
    </div>
</body>
</html>
