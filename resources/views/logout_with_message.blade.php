<!-- resources/views/logout_with_message.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
</head>
<body>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <script>
        // Submete o formulário automaticamente
        document.getElementById('logout-form').submit();
    </script>
</body>
</html>
