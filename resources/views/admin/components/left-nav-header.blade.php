<div class="leftbar-profile p-3 w-100">
    <div class="media position-relative">
        <div class="leftbar-user online">
            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" class="thumb-md rounded-circle" >
        </div>
        <div class="media-body align-self-center text-truncate ml-3">
            <h5 class="mt-0 mb-1 font-weight-semibold">{{ auth()->user()->name }}</h5>
            @if(auth()->user()->role==0)
            <p class="text-uppercase mb-0 font-12"> Type <span class="text-primary text-lowercase"> ( admin ) </span> </p>
            @elseif(auth()->user()->role==1)
            <p class="text-uppercase mb-0 font-12">  Type  <span class="text-primary text-lowercase"> ( company ) </span></p>
            @elseif(auth()->user()->role==2)
            <p class="text-uppercase mb-0 font-12">  Type  <span class="text-primary text-lowercase"> ( contact ) </span></p>
            @endif
        </div>
        <!--end media-body-->
    </div>
</div>