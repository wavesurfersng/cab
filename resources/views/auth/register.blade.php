@extends('layouts.app')

@section('title', 'Register - Music Platform')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">I want to</label>
                            <select class="form-select" id="role" name="role" required onchange="toggleArtistFields()">
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Listen to Music</option>
                                <option value="artist" {{ old('role') === 'artist' ? 'selected' : '' }}>Upload & Earn Money</option>
                            </select>
                        </div>

                        <div id="artistFields" style="display: none;">
                            <div class="mb-3">
                                <label for="stage_name" class="form-label">Stage Name</label>
                                <input type="text" class="form-control" id="stage_name" name="stage_name" value="{{ old('stage_name') }}">
                            </div>

                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <input type="text" class="form-control" id="genre" name="genre" value="{{ old('genre') }}" placeholder="e.g., Afrobeats, Hip Hop">
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                Artists need to pay a one-time upload fee to start uploading music and videos.
                                Earn <strong>NGN 5,000 for every 50,000 views</strong>!
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Account</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleArtistFields() {
    const role = document.getElementById('role').value;
    const artistFields = document.getElementById('artistFields');
    
    if (role === 'artist') {
        artistFields.style.display = 'block';
        document.getElementById('stage_name').required = true;
    } else {
        artistFields.style.display = 'none';
        document.getElementById('stage_name').required = false;
    }
}

// Initialize on page load
toggleArtistFields();
</script>
@endpush
