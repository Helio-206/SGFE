<x-app-layout>
    <x-slot name="header">
        Perfil
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
