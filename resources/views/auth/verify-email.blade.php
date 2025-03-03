<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .verify-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .verify-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .verify-icon {
            font-size: 48px;
            color: #3490dc;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="verify-container">
            <div class="verify-header">
                <div class="verify-icon">
                    📧
                </div>
                <h1>Verify Your Email Address</h1>
            </div>

            <div class="card-body">
                @if (session('message'))
                    <div class="alert alert-success" role="alert">
                        {{ session('message') }}
                    </div>
                @endif

                <p class="text-center mb-4">
                    Before proceeding, please check your email for a verification link.
                    The email has been sent to: <strong>{{ Auth::user()->wmsu_email }}</strong><br>
                    If you did not receive the email, click the button below to request another.
                </p>

                <form method="POST" action="{{ route('verification.send') }}" class="text-center">
                    @csrf
                    <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                </form>

                <div class="mt-4 text-center">
                    <form method="POST" action="{{ route('logout') }}" class="text-center">
                        @csrf
                        <button type="submit" class="btn btn-link">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
