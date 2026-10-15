@php
    $items = $benefitBalance['items'] ?? [];
@endphp
@if(count($items))
<ul class="mentee-benefits">
    @foreach($items as $item)
    <li class="mentee-benefits__item">
        <div class="mentee-benefits__top">
            <span class="mentee-benefits__label">{{ $item['label'] }}</span>
            <span class="mentee-benefits__left">{{ $item['left_label'] }}</span>
        </div>
        <span class="mentee-benefits__status mentee-benefits__status--{{ $item['status'] }}">{{ $item['status_label'] }}</span>
        @if(!empty($item['message']))
        <p class="mentee-benefits__msg">{{ $item['message'] }}</p>
        @endif
        @if(in_array($item['key'], ['career_counselling', 'in_house_mentor'], true))
        <div class="mentee-benefits__history">
            <div class="mentee-benefits__history-label">Usage history</div>
            @forelse($item['history'] ?? [] as $row)
            <div class="mentee-benefits__history-row">
                <div>
                    <strong>{{ $row['at'] }}</strong>
                    · {{ $row['with'] }}
                    · {{ $row['minutes'] }} min
                    @if(!empty($row['ref']))
                        · {{ $row['ref'] }}
                    @endif
                </div>
                <span class="mentee-benefits__history-flag {{ $row['counts'] ? 'is-counted' : 'is-open' }}">
                    {{ $row['counts'] ? 'Counted' : 'Not covered' }}
                </span>
            </div>
            @empty
            <p class="mentee-benefits__msg">No hours used in this period.</p>
            @endforelse
        </div>
        @endif
    </li>
    @endforeach
</ul>
@endif
