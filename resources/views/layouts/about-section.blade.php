@php use App\Models\Page;use Illuminate\Support\Str;$about=Page::find(1);
$short_desc=Str::limit(strip_tags($about->description),340);
@endphp
<section class="p-t-0 p-b-0">
    <div class="bg-overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 center text-left text-dark p-t-100">
                <h4>WHY CHOOSE</h4>
                <h1>WEBUYFONES ?</h1>
                <p class="lead">{{ $short_desc }}</p>
                @if(!Request::is('about-us'))
                    <a class="btn btn-dark btn-outline" href="{{ url('about-us') }}">Read More</a>
                @endif
            </div>
            <div class="col-lg-6">
                <img src="{{ asset('images/whywebuyfones.jpg') }}">
            </div>
        </div>
    </div>
</section>
