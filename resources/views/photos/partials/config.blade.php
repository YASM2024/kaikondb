<script>
  window.xCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  window.authenticated = @json(\Illuminate\Support\Facades\Auth::check());
  window.userId = @json(\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null);
  window.isEventListenerSet = false;
  window.homeUrl = @json(url('/'));
  window.photoBaseUrl = @json(route('photos'));
  window.profileUrl = @json(url('/storage/profile'));
  window.agreeUrl = @json(route('agree'));
  window.waitImg = @json(url('/storage/img/wait.png'));
</script>
