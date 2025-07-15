<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Domain
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->tenants as $tenant)
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900">
                                    {{ $tenant->name }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ $tenant->users->pluck('email')->implode(', ') }}
                                </td>
                                <td class="px-6 py-4">
                                    @foreach ($tenant->domains as $domain)
                                        {{ $domain->domain }} {{ $loop->last ? '' : ', ' }}
                                    @endforeach
                                </td>
                                <td class="px-6 py-4">

                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
