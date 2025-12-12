@extends('layouts.admin')

@section('title', __('product.Edit_Product'))
@section('content-header', __('product.Edit_Product'))

@section('content')

    <div class="card">
        <div class="card-body">

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">{{ __('product.Name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="{{ __('product.Name') }}" value="{{ old('name', $product->name) }}">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="form-group">
                    <label for="description">{{ __('product.Description') }}</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description"
                        placeholder="{{ __('product.Description') }}">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>{{ __('product.Image') }}</label>

                    <!-- PREVIEW -->
                    <div class="text-center mb-3">
                        <img id="preview"
                            src="{{ isset($product) && $product->image ? $product->image_url : asset('images/img-placeholder.jpg') }}"
                            class="img-thumbnail" style="max-height: 220px; width: auto;">
                    </div>

                    <!-- INPUT FILE (con la clase que necesita el plugin) -->
                    <div class="custom-file">
                        <input type="file" name="image" id="image" class="custom-file-input" accept="image/*">
                        
                        <label class="custom-file-label" for="image">
                            {{ isset($product) && $product->image ? basename($product->image) : __('product.Choose_file') }}
                        </label>
                    </div>

                    @error('image')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror

                    @if (isset($product) && $product->image)
                        <div class="mt-2">
                            <label class="text-danger">
                                <input type="checkbox" name="delete_image" value="1"> Eliminar imagen actual
                            </label>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="barcode">{{ __('product.Barcode') }}</label>
                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                        id="barcode" placeholder="{{ __('product.Barcode') }}"
                        value="{{ old('barcode', $product->barcode) }}">
                    @error('barcode')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price">{{ __('product.Price') }}</label>
                    <input type="text" name="price" class="form-control @error('price') is-invalid @enderror"
                        id="price" placeholder="{{ __('product.Price') }}"
                        value="{{ old('price', $product->price) }}">
                    @error('price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price_bsd">
                        Precio en USD (Pago en Bolivares)
                        <small class="text-muted">(opcional – si es diferente al precio normal)</small>
                    </label>
                    <input type="number" step="0.01" name="price_bsd"
                        class="form-control @error('price_bsd') is-invalid @enderror" id="price_bsd" placeholder="Ej: 28.50"
                        value="{{ old('price_bsd', $product->price_bsd ?? '') }}">
                    @error('price_bsd')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="quantity">{{ __('product.Quantity') }}</label>
                    <input type="text" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                        id="quantity" placeholder="{{ __('product.Quantity') }}"
                        value="{{ old('quantity', $product->quantity) }}">
                    @error('quantity')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">{{ __('product.Status') }}</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                        <option value="1" {{ old('status', $product->status) === 1 ? 'selected' : '' }}>
                            {{ __('common.Active') }}</option>
                        <option value="0" {{ old('status', $product->status) === 0 ? 'selected' : '' }}>
                            {{ __('common.Inactive') }}</option>
                    </select>
                    @error('status')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button class="btn btn-primary" type="submit">{{ __('common.Update') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            bsCustomFileInput.init();

            const input = document.getElementById('image');
            const preview = document.getElementById('preview');

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Si se marca "eliminar imagen"
            document.querySelectorAll('input[name="delete_image"]').forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        preview.src = '{{ asset('images/img-placeholder.jpg') }}';
                        // También resetea el input file
                        input.value = '';
                        bsCustomFileInput.init(); // reinicia el label
                    }
                });
            });
        });
    </script>
@endsection
