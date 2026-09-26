<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin sign in | Ceylon Vacation Venues</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
        <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.2),_transparent_38%),linear-gradient(145deg,#061a33_0%,#082947_48%,#06425a_100%)]"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-56 bg-[linear-gradient(175deg,transparent_48%,rgba(255,255,255,0.07)_49%,rgba(255,255,255,0.02)_75%)]"></div>

        <section class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-black/30 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="relative hidden min-h-[620px] flex-col justify-between overflow-hidden bg-[#082f57] p-12 text-white lg:flex">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_75%_20%,rgba(56,189,248,0.28),transparent_34%),linear-gradient(160deg,transparent_48%,rgba(3,105,161,0.38)_49%,rgba(8,47,87,0.05)_78%)]"></div>
                <div class="relative flex items-center gap-4">
                    <div class="grid size-14 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                        <svg aria-hidden="true" viewBox="0 0 64 64" class="size-9 fill-none stroke-[#f4c667]" stroke-width="2.8" stroke-linecap="round">
                            <path d="M32 52V24M32 28C22 27 17 21 15 14c8 0 14 3 17 10M32 28c9-2 15-8 17-16-9 1-14 5-17 12M32 34c-8 0-13-3-17-8 8-2 14 0 17 5M32 34c8-1 13-4 17-9-8-1-14 1-17 6M22 53c6-4 14-4 20 0"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold tracking-wide">Ceylon Vacation Venues</p>
                        <p class="text-sm text-sky-100/75">Sri Lanka, thoughtfully explored</p>
                    </div>
                </div>

                <div class="relative max-w-md">
                    <p class="mb-5 text-sm font-semibold uppercase tracking-[0.24em] text-[#f4c667]">Administration</p>
                    <h1 class="text-4xl font-semibold leading-tight tracking-tight">Manage memorable journeys from one secure place.</h1>
                    <p class="mt-5 leading-7 text-sky-100/80">Access is limited to authorised Ceylon Vacation Venues team members.</p>
                </div>

                <p class="relative text-sm text-sky-100/60">© {{ date('Y') }} Ceylon Vacation Venues</p>
            </div>

            <div class="flex min-h-[620px] items-center px-6 py-12 sm:px-12 lg:px-14">
                <div class="mx-auto w-full max-w-sm">
                    <div class="mb-10 flex items-center gap-3 lg:hidden">
                        <div class="grid size-11 place-items-center rounded-xl bg-[#082f57]">
                            <svg aria-hidden="true" viewBox="0 0 64 64" class="size-7 fill-none stroke-[#f4c667]" stroke-width="3" stroke-linecap="round">
                                <path d="M32 52V24M32 28C22 27 17 21 15 14c8 0 14 3 17 10M32 28c9-2 15-8 17-16-9 1-14 5-17 12M22 53c6-4 14-4 20 0"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-[#082f57]">Ceylon Vacation Venues</p>
                    </div>

                    <div class="mb-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-sky-700">Secure admin portal</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Welcome back</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Sign in with your authorised administrator account.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-600 focus:ring-4 focus:ring-sky-100"
                                placeholder="admin@example.com">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                            </div>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-600 focus:ring-4 focus:ring-sky-100"
                                placeholder="Enter your password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-sky-700 focus:ring-sky-600">
                            Keep me signed in on this device
                        </label>

                        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-[#082f57] px-4 py-3.5 font-semibold text-white shadow-lg shadow-sky-950/15 transition hover:bg-[#0b4278] focus:outline-none focus:ring-4 focus:ring-sky-200">
                            Sign in to administration
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs leading-5 text-slate-400">Protected access. Login activity may be monitored for security.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
