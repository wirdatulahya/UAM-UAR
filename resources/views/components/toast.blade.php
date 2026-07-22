@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        @php
            $toasts = [];
            if (session('success')) $toasts[] = ['type' => 'success', 'icon' => 'bi-check-circle-fill', 'title' => 'Success', 'message' => session('success')];
            if (session('error')) $toasts[] = ['type' => 'danger', 'icon' => 'bi-x-circle-fill', 'title' => 'Error', 'message' => session('error')];
            if (session('warning')) $toasts[] = ['type' => 'warning', 'icon' => 'bi-exclamation-triangle-fill', 'title' => 'Warning', 'message' => session('warning')];
            if (session('info')) $toasts[] = ['type' => 'info', 'icon' => 'bi-info-circle-fill', 'title' => 'Info', 'message' => session('info')];
            
            // Collect the first validation error if any
            if ($errors->any()) {
                $toasts[] = [
                    'type' => 'danger',
                    'icon' => 'bi-exclamation-circle-fill',
                    'title' => 'Validation Error',
                    'message' => $errors->first()
                ];
            }
        @endphp

        @foreach($toasts as $toast)
            @php
                $bgColors = [
                    'success' => '#15803d',
                    'danger'  => '#b91c1c',
                    'warning' => '#b45309',
                    'info'    => '#0369a1',
                ];
                $bgColor = $bgColors[$toast['type']] ?? '#0B2E6D'; // Default to secondary
            @endphp
            <div class="toast align-items-center border-0 mb-2 animate-in" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: {{ $bgColor }} !important; color: #fff; box-shadow: var(--card-shadow); border-radius: 12px; opacity: 1 !important;">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2" style="font-weight: 600; font-size: 0.88rem;">
                        <i class="bi {{ $toast['icon'] }} fs-5"></i>
                        <span>{{ $toast['message'] }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" style="opacity: 0.8;"></button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl, { delay: 4000 })
            })
            toastList.forEach(toast => toast.show());
        });
    </script>
@endif
