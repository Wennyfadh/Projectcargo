<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · CargoTrace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-[#0b2036] p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full border border-cyan-300/20"></div>
            <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full border border-cyan-300/20"></div>
            <a href="{{ route('login') }}" class="relative flex items-center gap-3 text-xl font-bold"><span class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-600">CT</span> CargoTrace</a>
            <div class="relative max-w-xl">
                <p class="mb-4 text-xs font-bold uppercase tracking-[.25em] text-cyan-300">Mayora global logistics</p>
                <h1 class="text-5xl font-bold leading-tight">Visibility for every shipment, from origin to destination.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-300">Track ocean freight, monitor delivery performance, and keep your logistics team aligned in one place.</p>
                <div class="mt-9 flex gap-3 text-sm text-slate-300"><span class="rounded-full border border-white/15 px-4 py-2">Shipment tracking</span><span class="rounded-full border border-white/15 px-4 py-2">Exception management</span></div>
            </div>
            <p class="relative text-xs text-slate-400">CargoTrace · Shipment visibility platform</p>
        </section>
        <section class="flex items-center justify-center p-5 sm:p-10">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-10 flex items-center gap-3 text-lg font-bold text-[#0b2036] lg:hidden"><span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan-700 text-white">CT</span> CargoTrace</a>
                <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/60 sm:p-9">
                    <p class="text-sm font-semibold uppercase tracking-widest text-cyan-700">Welcome back</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">Sign in to CargoTrace</h2>
                    <p class="mt-2 text-sm text-slate-500">Use your account to access shipment operations.</p>
                    @if($errors->any())
                        <div role="alert" class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email', 'admin@admin.com') }}" autocomplete="username" required autofocus
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-3 outline-none transition focus:border-cyan-600 focus:ring-4 focus:ring-cyan-600/10" placeholder="you@company.com">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold">Password</label>
                            <input id="password" name="password" type="password" value="password" autocomplete="current-password" required
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-3 outline-none transition focus:border-cyan-600 focus:ring-4 focus:ring-cyan-600/10" placeholder="Enter your password">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-600"> Remember me</label>
                        <button class="w-full rounded-lg bg-cyan-700 px-4 py-3 font-semibold text-white shadow-md shadow-cyan-900/15 hover:bg-cyan-800 focus:outline-none focus:ring-4 focus:ring-cyan-700/20 transition active:scale-98">Sign in</button>
                    </form>
                </div>
                <div class="mt-5 rounded-xl border border-slate-200 bg-white/90 p-4 text-xs leading-5 text-slate-600 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                        <span class="font-bold text-slate-800">Akun Demo Cepat · Password: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-cyan-800">password</code></span>
                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">Ready</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span>Admin: <code>admin@admin.com</code></span>
                            <button type="button" onclick="fillCreds('admin@admin.com', 'password')" class="rounded bg-slate-100 hover:bg-cyan-100 text-cyan-800 px-2 py-0.5 font-bold transition">Isi Form</button>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Admin Mayora: <code>admin@cargotrace.com</code></span>
                            <button type="button" onclick="fillCreds('admin@cargotrace.com', 'password')" class="rounded bg-slate-100 hover:bg-cyan-100 text-cyan-800 px-2 py-0.5 font-bold transition">Isi Form</button>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Operator: <code>operator@cargotrace.mayora.co.id</code></span>
                            <button type="button" onclick="fillCreds('operator@cargotrace.mayora.co.id', 'password')" class="rounded bg-slate-100 hover:bg-cyan-100 text-cyan-800 px-2 py-0.5 font-bold transition">Isi Form</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function fillCreds(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
