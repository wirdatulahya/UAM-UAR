<?php

$dir = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$searchIcon = '<div style="width:32px;height:32px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-person-fill" style="color:#fff;font-size:.9rem;"></i>
                    </div>';

$replaceIcon = '<div style="width:32px;height:32px;background:var(--secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset(\'storage/\' . Auth::user()->profile_photo_path) }}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i class="bi bi-person-fill" style="color:#fff;font-size:.9rem;"></i>
                        @endif
                    </div>';

$searchMenu = '<a href="{{ route(\'password.change\') }}"';

$replaceMenu = '<a href="{{ route(\'profile.index\') }}"
                        style="display:flex;align-items:center;gap:.65rem;padding:.72rem 1rem;font-size:.85rem;font-weight:500;color:var(--text);text-decoration:none;transition:background var(--transition);"
                        onmouseenter="this.style.background=\'var(--secondary-light)\';this.style.color=\'var(--secondary)\';"
                        onmouseleave="this.style.background=\'\';this.style.color=\'var(--text)\';">
                        <i class="bi bi-person-circle" style="font-size:.9rem;color:var(--text-muted);"></i>
                        My Profile
                    </a>

                    <a href="{{ route(\'password.change\') }}"';

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'profile') === false) {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $changed = false;
        
        if (strpos($content, $searchIcon) !== false) {
            $content = str_replace($searchIcon, $replaceIcon, $content);
            $changed = true;
        }
        
        // Ensure we don't insert "My Profile" multiple times
        if (strpos($content, $searchMenu) !== false && strpos($content, 'My Profile') === false) {
            $content = str_replace($searchMenu, $replaceMenu, $content);
            $changed = true;
        }

        if ($changed) {
            file_put_contents($path, $content);
            echo "Updated: $path\n";
        }
    }
}
echo "Done.\n";
