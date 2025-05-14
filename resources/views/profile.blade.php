<x-layout>
  <div class="container">
    <div class="row">
        <div class="col-12 col-md-6">
            <form action="{{ route('profile.update', ['user' => Auth::user()]) }}" method="POST">
                @method('PUT')
                @csrf
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Indirizzo email</label>
                    <input type="email" value="{{$email}}" name="email" class="form-control" id="exampleFormControlInput1">
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" value="{{$name}}" name="name" class="form-control" id="name">
                </div>
                {{-- password --}}
                <div class="mb-3">
                    <label for="passwordOld" class="form-label">Password corrente</label>
                    <input type="password" value="{{$passwordOld}}" name="passwordOld" class="form-control" id="passwordOld">
                </div>
                <div class="mb-3">
                    <label for="passwordNew" class="form-label">Password nuova</label>
                    <input type="password" value="{{$passwordNew}}" name="passwordNew" class="form-control" id="passwordNew">
                </div>
                <div>
                    <button type="submit" class="btn btn-danger">Modifica</button>
                </div>
            </form>
        </div>
    </div>
  </div>
</x-layout>