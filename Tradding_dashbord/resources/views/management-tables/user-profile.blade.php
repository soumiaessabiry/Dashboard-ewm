@extends('layouts.user_type.auth')

@section('content')
    <div>
        @if (session('profile_success'))
            <div class="alert alert-success">
                {{ session('profile_success') }}
            </div>
        @endif
        <div class="page-header min-height-200 border-radius-xl mt-4" style="background: #0a8897">
            <span class="mask navbar-verbackgroundtical opacity-6"></span>
            <div class="card card-body blur shadow-blur mx-4 mt-n6">
                <div class="row gx-4">
                    <div class="col-auto">
                        <div class="avatar avatar-xl position-relative">
                            <img src="../assets/img/Profile_User.jpeg" alt="..."
                                class="w-100 border-radius-lg shadow-sm">

                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                                {{ Auth::user()->username }}
                            </h5>
                            <p class="mb-0 font-weight-bold text-sm capitalize">
                                {{ Auth::user()->role }}
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                        <div class="nav-wrapper position-relative end-0">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header pb-0 px-3">
                <h6 class="mb-0">{{ __('Informations de Profil') }}</h6>
            </div>
            <div class="card-body pt-4 p-3">
                <form action="{{ route('user-profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Nom Complet</label>
                                <input class="form-control" type="text" placeholder="Nom Complet" id="username"
                                    name="username" value="{{ Auth::user()->username }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Email</label>
                                <input class="form-control" type="email" placeholder="Email" id="email" name="email"
                                    value="{{ Auth::user()->email }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Mot de passe</label>
                                <input class="form-control" type="password" placeholder="Mot de passe" id="password"
                                    name="password">
                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">Modifer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
