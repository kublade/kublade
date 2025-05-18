@php
    $parents = $parents ?? [];
    $mapped = $mapped ?? collect();
@endphp

<ul {!! empty($parents) ? 'class="w-100 permission-tree permission-tree--first"' : 'class="permission-tree"' !!}>
    @foreach ($permissions as $permission => $children)
        <li>
            <div class="form-group d-flex gap-2 align-items-center">
                <input id="{{ !empty($parents) ? implode('.', $parents) . '.' : '' }}{{ $permission }}" type="checkbox" class="form-check-input mt-0 {{ !empty($children) ? 'bg-light disabled' : '' }} @error('permissions') is-invalid @enderror" name="permissions[]" value="{{ !empty($parents) ? implode('.', $parents) . '.' : '' }}{{ $permission }}" {{ $mapped->contains((!empty($parents) ? implode('.', $parents) . '.' : '') . $permission) ? 'checked' : '' }}>
                <label for="{{ !empty($parents) ? implode('.', $parents) . '.' : '' }}{{ $permission }}" class="text-nowrap">{!! \App\Helpers\PermissionSet::translate((!empty($parents) ? implode('.', $parents) . '.' : '') . $permission) !!}</label>
            </div>
            @if (!empty($children))
                @include('role.permission-tree', ['permissions' => $children, 'parents' => array_merge($parents ?? [], [$permission])])
            @endif
        </li>
    @endforeach
</ul>
