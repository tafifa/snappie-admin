<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <x-slot name="description">
            Quick access to API endpoints configured in your environment
        </x-slot>

        <div class="fi-wi-api-redirect grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
            @foreach($links as $link)
                <a 
                    href="{{ $link['url'] }}" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="fi-wi-api-link group block p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-{{ $link['color'] }}-300 dark:hover:border-{{ $link['color'] }}-500 hover:shadow-md transition-all duration-200"
                >
                    <div class="flex items-center gap-x-4">
                        <x-filament::icon 
                            :icon="$link['icon']" 
                            class="fi-wi-api-link-icon h-6 w-6 text-{{ $link['color'] }}-500 group-hover:text-{{ $link['color'] }}-600" 
                        />
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="fi-wi-api-link-title text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-{{ $link['color'] }}-700 truncate">
                                {{ $link['label'] }}
                            </h3>
                            
                            <p class="fi-wi-api-link-desc text-xs text-gray-400 dark:text-gray-500 group-hover:text-{{ $link['color'] }}-500 truncate mt-1">
                                {{ $link['description'] ?? '' }}
                            </p>
                        </div>

                        <x-filament::icon 
                            icon="heroicon-o-arrow-top-right-on-square"
                            class="fi-wi-api-link-icon h-6 w-6 text-gray-400 group-hover:text-gray-600" 
                        />
                    </div>
                </a>
            @endforeach
        </div>

        @if(empty($links))
            <div class="fi-wi-api-redirect-empty text-center py-8">
                <x-filament::icon 
                    icon="heroicon-o-link" 
                    class="h-12 w-12 text-gray-400 mx-auto mb-4" 
                />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No API endpoints configured. Check your environment variables.
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>