<x-filament-panels::page>
    @livewire(\Filament\Widgets\AccountWidget::class)

    <x-filament-widgets::widgets
        :widgets="$this->getVisibleWidgets()"
        :columns="$this->getColumns()"
    />
</x-filament-panels::page>
