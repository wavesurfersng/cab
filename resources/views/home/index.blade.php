@extends('layouts.app')

@section('title', 'Home - Music Platform')

@section('content')
<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center text-white">
            <h1 class="display-4 fw-bold">Welcome to MusicPlatform</h1>
            <p class="lead">Discover amazing music and videos from talented artists</p>
            <p class="text-warning"><i class="fas fa-coins"></i> Artists earn NGN 5,000 for every 50,000 views!</p>
        </div>
    </div>

    <!-- Featured Tracks -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="fas fa-fire"></i> Featured Tracks</h2>
        </div>
        @foreach($featuredTracks as $track)
            <div class="col-md-3 mb-4">
                <div class="card h-100 music-card">
                    @if($track->cover_art)
                        <img src="{{ asset('storage/' . $track->cover_art) }}" class="card-img-top" alt="{{ $track->title }}">
                    @else
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-music fa-3x text-white"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $track->title }}</h5>
                        <p class="card-text text-muted">
                            <a href="{{ route('artist.profile', $track->artist->stage_name) }}" class="text-decoration-none">
                                {{ $track->artist->stage_name }}
                            </a>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-play"></i> {{ number_format($track->views) }} views
                            </small>
                            <a href="{{ route('track.show', $track->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-play"></i> Play
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Featured Videos -->
    @if($featuredVideos->count() > 0)
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="fas fa-video"></i> Featured Videos</h2>
        </div>
        @foreach($featuredVideos as $video)
            <div class="col-md-4 mb-4">
                <div class="card h-100 video-card">
                    <div class="position-relative">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" class="card-img-top" alt="{{ $video->title }}">
                        @else
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-video fa-3x text-white"></i>
                            </div>
                        @endif
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <a href="{{ route('video.show', $video->id) }}" class="btn btn-light rounded-circle btn-lg">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $video->title }}</h5>
                        <p class="card-text text-muted">{{ $video->artist->stage_name }}</p>
                        <small class="text-muted">
                            <i class="fas fa-eye"></i> {{ number_format($video->views) }} views
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Trending Artists -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="fas fa-star"></i> Trending Artists</h2>
        </div>
        @foreach($trendingArtists as $artist)
            <div class="col-md-2 mb-4">
                <div class="card artist-card text-center">
                    <div class="card-body">
                        @if($artist->profile_picture)
                            <img src="{{ asset('storage/' . $artist->profile_picture) }}" class="profile-picture mb-3" alt="{{ $artist->stage_name }}">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        @endif
                        <h6 class="card-title">{{ $artist->stage_name }}</h6>
                        <p class="card-text small text-muted">
                            <i class="fas fa-eye"></i> {{ number_format($artist->total_views) }} views
                        </p>
                        <a href="{{ route('artist.profile', $artist->stage_name) }}" class="btn btn-sm btn-outline-primary">View Profile</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Recent Posts / General Feed -->
    <div class="row">
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="fas fa-newspaper"></i> Latest Updates</h2>
        </div>
        @forelse($recentPosts as $post)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @if($post->artist->profile_picture)
                                <img src="{{ asset('storage/' . $post->artist->profile_picture) }}" class="rounded-circle me-3" width="50" height="50">
                            @else
                                <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $post->artist->stage_name }}</h6>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <p class="card-text">{{ $post->content }}</p>
                        @if($post->media_url)
                            @if($post->media_type === 'image')
                                <img src="{{ asset('storage/' . $post->media_url) }}" class="img-fluid rounded mb-3">
                            @elseif($post->media_type === 'video')
                                <video controls class="w-100 rounded">
                                    <source src="{{ asset('storage/' . $post->media_url) }}" type="video/mp4">
                                </video>
                            @endif
                        @endif
                        <div class="d-flex gap-3">
                            <button class="btn btn-sm btn-outline-danger like-btn" 
                                    data-type="App\Models\Post" 
                                    data-id="{{ $post->id }}">
                                <i class="far fa-heart"></i> {{ $post->likes_count }}
                            </button>
                            <span class="btn btn-sm btn-outline-primary">
                                <i class="far fa-comment"></i> {{ $post->comments_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-white">No posts yet.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.like-btn').click(function() {
        const btn = $(this);
        const likeableType = btn.data('type');
        const likeableId = btn.data('id');
        
        $.ajax({
            url: '{{ route("like.toggle") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                likeable_type: likeableType,
                likeable_id: likeableId
            },
            success: function(response) {
                if (response.liked) {
                    btn.find('i').removeClass('far').addClass('fas');
                } else {
                    btn.find('i').removeClass('fas').addClass('far');
                }
            }
        });
    });
});
</script>
@endpush
