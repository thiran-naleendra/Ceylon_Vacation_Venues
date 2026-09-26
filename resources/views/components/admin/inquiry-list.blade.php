@props(['inquiries'])

@if ($inquiries->isEmpty())
    <div class="px-5 py-14 text-center sm:px-8">
        <div class="mx-auto grid size-12 place-items-center rounded-full bg-slate-100 text-slate-400">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-6 fill-none stroke-current" stroke-width="1.8">
                <path d="M4 5h16v12H8l-4 4V5Zm4 4h8m-8 4h5" />
            </svg>
        </div>
        <p class="mt-4 font-semibold text-slate-800">No inquiries yet</p>
        <p class="mt-1 text-sm text-slate-500">New customer inquiries will appear here.</p>
    </div>
@else
    <div class="divide-y divide-slate-100 lg:hidden">
        @foreach ($inquiries as $inquiry)
            <article class="min-w-0 p-4 sm:p-5">
                <div class="flex min-w-0 items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900">{{ $inquiry->name }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $inquiry->email }}</p>
                    </div>
                    <x-admin.status-badge :status="$inquiry->status" />
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Type</p>
                        <p class="mt-1 truncate text-slate-700">{{ $inquiry->type->label() }}</p>
                    </div>
                    <div class="min-w-0 text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Received</p>
                        <time datetime="{{ $inquiry->created_at->toIso8601String() }}"
                            class="mt-1 block text-slate-700">{{ $inquiry->created_at->diffForHumans() }}</time>
                    </div>
                </div>
                <p class="mt-3 break-words text-sm text-slate-600">{{ $inquiry->subject ?: $inquiry->type->label() . ' inquiry' }}
                </p>
            </article>
        @endforeach
    </div>

    <div class="hidden overflow-x-auto lg:block">
        <table class="w-full min-w-[760px] table-fixed text-left">
            <thead
                class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th scope="col" class="w-[25%] px-6 py-3.5">Customer</th>
                    <th scope="col" class="w-[16%] px-6 py-3.5">Type</th>
                    <th scope="col" class="w-[19%] px-6 py-3.5">Status</th>
                    <th scope="col" class="w-[22%] px-6 py-3.5">Assigned to</th>
                    <th scope="col" class="w-[18%] px-6 py-3.5">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-sm">
                @foreach ($inquiries as $inquiry)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="truncate font-semibold text-slate-900">{{ $inquiry->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $inquiry->email }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $inquiry->type->label() }}</td>
                        <td class="px-6 py-4"><x-admin.status-badge :status="$inquiry->status" /></td>
                        <td class="truncate px-6 py-4 text-slate-600">{{ $inquiry->assignedUser?->name ?: 'Unassigned' }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            <time
                                datetime="{{ $inquiry->created_at->toIso8601String() }}">{{ $inquiry->created_at->format('M j, Y') }}</time>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif