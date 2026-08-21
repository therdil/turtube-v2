<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Hesap Silme
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Hesabınızı silme işlemi hakkında ayrıntılı bilgi ve yönlendirmeler.
        </p>
    </div>

    <a href="{{ route('account.delete') }}"
       class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">
        Hesap Silme Sayfası
    </a>
</div>

@include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
