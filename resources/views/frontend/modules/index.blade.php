@extends('frontend.layouts.master')

@section('page_title', 'Home')

@section('banner')
    @include('frontend.includes.banner')
@endsection

@section('contents')

    @if ($post_data->isEmpty())
        <div class="alert alert-danger" role="alert">
            <p>Post not available</p>
        </div>
    @else
        @foreach ($post_data as $post)
            <div class="col-lg-12">
                <div class="blog-post">
                    <div class="blog-thumb">
                        <img src="{{ $post->photo }}" alt="">
                    </div>
                    <div class="down-content">
                        <span class="text-success">{{ $post->category?->name }} <sub
                                class="text-warning">{{ $post->sub_category?->name }}</sub> </span>
                        <a href="{{ route('front.single', $post->slug) }}">
                            <h4>{{ $post->title }}</h4>
                        </a>

                        @php
                            $commentCount = $post->comment->count();
                            $postViews = $post->post_count?->count | 0;
                        @endphp

                        <ul class="post-info">
                            <li><a>{{ $post->user?->name }}</a></li>
                            <li><a>{{ $post->created_at->format('M d, Y') }}</a></li>
                            <li><a>{{ $commentCount }} {{ $commentCount > 1 ? 'comments' : 'comment' }}</a>
                            </li>
                            <li><a>{{ $postViews }} {{ $postViews > 1 ? 'views' : 'view' }}</a></li>
                        </ul>
                        <p>{{ strip_tags(Str::substr($post->description, 0, 400)) . '..' }}
                            <a href="{{ route('front.single', $post->slug) }}"><button class="read-more-button">Read
                                    more</button></a>
                        </p>
                        <div class="post-options">
                            <div class="row">
                                <div class="col-6">
                                    <ul class="post-tags">
                                        <li><i class="fa fa-tags"></i></li>
                                        @if ($post->tag->isEmpty())
                                            <div class="alert alert-danger" role="alert">
                                                <p>No tag found.</p>
                                            </div>
                                        @else
                                            @foreach ($post->tag as $tag)
                                                <li><a
                                                        href="{{ route('front.tag', $tag->slug) }}">{{ $tag->name }}</a>,
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div class="col-6">
                                    <ul class="post-share">
                                        <li><i class="fa fa-share-alt"></i></li>
                                        <li><a href="#">Facebook</a>,</li>
                                        <li><a href="#"> Twitter</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif


    <div class="col-lg-12">
        <div class="main-button">
            <a href="{{ route('front.all_post') }}">View All Posts</a>
        </div>
    </div>
@endsection
