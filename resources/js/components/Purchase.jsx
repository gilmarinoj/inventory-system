import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Purchase extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            suppliers: [],
            search: "",
            supplier_id: "",
            purchase_date: new Date().toISOString().split("T")[0],
            status: "completed",
            notes: "",
            parallel_rate_used: "",
            translations: {},
        };

        this.loadCart = this.loadCart.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.loadSuppliers = this.loadSuppliers.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.addProductToCart = this.addProductToCart.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleChangePrice = this.handleChangePrice.bind(this);
        this.handleClickDelete = this.handleClickDelete.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);
        this.setSupplierId = this.setSupplierId.bind(this);
        this.handleDateChange = this.handleDateChange.bind(this);
        this.handleStatusChange = this.handleStatusChange.bind(this);
        this.handleNotesChange = this.handleNotesChange.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        this.loadTranslations();
        this.loadSuppliers();
        this.loadProducts();
        this.loadCart();
    }

    loadTranslations() {
        axios
            .get("/admin/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
                this.setState({ translations: {} });
            });
    }

    loadSuppliers() {
        axios
            .get(`/admin/suppliers`, {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            })
            .then((res) => {
                console.log("Suppliers API Response:", res.data);
                console.log("Is Array?", Array.isArray(res.data));
                console.log("Has data property?", res.data.data);

                // Handle both array and paginated response
                const suppliers = Array.isArray(res.data)
                    ? res.data
                    : res.data.data || [];
                console.log("Final suppliers:", suppliers);

                this.setState({ suppliers });
            })
            .catch((error) => {
                console.error("Error loading suppliers:", error);
                this.setState({ suppliers: [] });
            });
    }

    loadProducts(search = "") {
        const query = !!search ? `?search=${search}` : "";
        axios
            .get(`/admin/products${query}`, {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            })
            .then((res) => {
                const products = res.data.data || [];
                this.setState({ products });
            })
            .catch((error) => {
                console.error("Error loading products:", error);
                this.setState({ products: [] });
            });
    }

    loadCart() {
        axios
            .get("/admin/purchase-cart", {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            })
            .then((res) => {
                const cart = Array.isArray(res.data) ? res.data : [];
                this.setState({ cart });
            })
            .catch((error) => {
                console.error("Error loading cart:", error);
                this.setState({ cart: [] });
            });
    }

    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }

    handleSearch(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    handleParallelRateChange = (e) => {
        this.setState({ parallel_rate_used: e.target.value });
    };

    addProductToCart(product) {
        // Check if product already in cart
        let cartItem = this.state.cart.find((c) => c.id === product.id);

        if (cartItem) {
            // Update quantity
            this.setState({
                cart: this.state.cart.map((c) => {
                    if (c.id === product.id) {
                        c.pivot.quantity = c.pivot.quantity + 1;
                    }
                    return c;
                }),
            });
        } else {
            // Add new item with purchase price
            const newProduct = {
                ...product,
                pivot: {
                    quantity: 1,
                    purchase_price: product.purchase_price || 0,
                    product_id: product.id,
                    user_id: 1,
                },
            };
            this.setState({ cart: [...this.state.cart, newProduct] });
        }

        // Sync with backend
        axios
            .post("/admin/purchase-cart", { barcode: product.barcode })
            .then((res) => {
                console.log("Added to cart:", res.data);
            })
            .catch((err) => {
                Swal.fire(
                    "Error!",
                    err.response?.data?.message || "Failed to add product",
                    "error"
                );
            });
    }

    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });
        if (!qty) return;

        axios
            .post("/admin/purchase-cart/change-qty", {
                product_id,
                quantity: qty,
            })
            .then((res) => {
                console.log("Quantity updated");
            })
            .catch((err) => {
                Swal.fire(
                    "Error!",
                    err.response?.data?.message || "Failed to update quantity",
                    "error"
                );
            });
    }

    handleChangePrice(product_id, price) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.purchase_price = price;
            }
            return c;
        });

        this.setState({ cart });
        if (!price) return;

        axios
            .post("/admin/purchase-cart/change-price", {
                product_id,
                purchase_price: price,
            })
            .then((res) => {
                console.log("Price updated");
            })
            .catch((err) => {
                Swal.fire(
                    "Error!",
                    err.response?.data?.message || "Failed to update price",
                    "error"
                );
            });
    }

    getTotal(cart) {
        const total = cart.map(
            (c) => c.pivot.quantity * (c.pivot.purchase_price || 0)
        );
        return sum(total).toFixed(2);
    }

    handleClickDelete(product_id) {
        axios
            .post("/admin/purchase-cart/delete", {
                product_id,
                _method: "DELETE",
            })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            })
            .catch((err) => {
                Swal.fire(
                    "Error!",
                    err.response?.data?.message || "Failed to delete",
                    "error"
                );
            });
    }

    handleEmptyCart() {
        axios
            .post("/admin/purchase-cart/empty", { _method: "DELETE" })
            .then((res) => {
                this.setState({ cart: [] });
            })
            .catch((err) => {
                Swal.fire(
                    "Error!",
                    err.response?.data?.message || "Failed to empty cart",
                    "error"
                );
            });
    }

    setSupplierId(event) {
        this.setState({ supplier_id: event.target.value });
    }

    handleDateChange(event) {
        this.setState({ purchase_date: event.target.value });
    }

    handleStatusChange(event) {
        this.setState({ status: event.target.value });
    }

    handleNotesChange(event) {
        this.setState({ notes: event.target.value });
    }

    handleClickSubmit() {
        const {
            supplier_id,
            purchase_date,
            status,
            notes,
            cart,
            suppliers,
            parallel_rate_used,
        } = this.state;

        // Validation
        if (!supplier_id) {
            Swal.fire("Error!", "Por favor seleccione un proveedor", "error");
            return;
        }

        if (cart.length === 0) {
            Swal.fire(
                "Error!",
                "Por favor seleccione al menos un producto",
                "error"
            );
            return;
        }

        if (!parallel_rate_used || parallel_rate_used <= 0) {
            Swal.fire(
                "Error!",
                "Debes ingresar la tasa paralela que te cobró el proveedor",
                "error"
            );
            return;
        }

        const total_amount = this.getTotal(cart);
        const items = cart.map((c) => ({
            product_id: c.id,
            quantity: c.pivot.quantity,
            purchase_price: c.pivot.purchase_price || 0,
        }));

        // Get supplier info safely
        const suppliersList = Array.isArray(suppliers) ? suppliers : [];
        const supplier = suppliersList.find((s) => s.id == supplier_id);
        const supplierName = supplier
            ? `${supplier.first_name} ${supplier.last_name}`
            : "Unknown";

        Swal.fire({
            title: "Confirmar Compra",
            html: `
                <div style="text-align: left;">
                    <p><strong>Proveedor:</strong> ${supplierName}</p>
                    <p><strong>Fecha:</strong> ${purchase_date}</p>
                    <p><strong>Monto Total:</strong> ${window.APP.currency_symbol} ${total_amount}</p>
                    <p><strong>Estado:</strong> ${status}</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: "Guardar Compra",
            cancelButtonText: "Cancelar",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return axios
                    .post("/admin/purchases", {
                        supplier_id,
                        purchase_date,
                        total_amount,
                        status,
                        notes,
                        parallel_rate_used,
                        items,
                    })
                    .then((res) => {
                        this.loadCart();
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(
                            err.response?.data?.message ||
                                "Fallo al realizar la compra!"
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                Swal.fire(
                    "Perfecto!",
                    "Compra creada satisfactoriamente!",
                    "success"
                );
                // Clear form
                this.setState({
                    cart: [],
                    supplier_id: "",
                    purchase_date: new Date().toISOString().split("T")[0],
                    status: "completed",
                    notes: "",
                });
            }
        });
    }

    getParallelRate() {
        return parseFloat(this.state.parallel_rate_used) || window.paraleloRate;
    }

    calcularCostoRealBcv(precioUsd) {
        const precio = parseFloat(precioUsd) || 0;
        const paralelo = this.getParallelRate();
        const bcv = window.dolarBcv || paralelo;
        if (bcv === 0) return precio.toFixed(2);
        return ((precio * paralelo) / bcv).toFixed(2);
    }

    diferenciaPorcentual(precioProv) {
        const prov = parseFloat(precioProv) || 0;
        if (prov === 0) return "";
        const real = this.calcularCostoRealBcv(prov);
        const diff = ((real / prov - 1) * 100).toFixed(1);
        return diff > 5 ? `(+${diff}%)` : "";
    }

    render() {
        const {
            cart = [],
            products = [],
            suppliers = [],
            search = "",
            supplier_id,
            purchase_date,
            status,
            notes,
            translations = {},
        } = this.state;

        // Ensure suppliers is always an array
        const suppliersList = Array.isArray(suppliers) ? suppliers : [];

        return (
            <div className="row purchase-container">
                {/* LEFT SIDE - Product Selector */}
                <div className="col-lg-8 col-md-7">
                    <div className="card">
                        <div className="card-body">
                            <div className="product-search mb-3">
                                <input
                                    type="text"
                                    className="form-control form-control-lg"
                                    placeholder={
                                        (translations["buscar_producto"] ||
                                            "Buscar Producto") + "..."
                                    }
                                    value={search}
                                    onChange={this.handleChangeSearch}
                                    onKeyDown={this.handleSearch}
                                />
                            </div>
                            <div className="order-product">
                                {products.map((p) => (
                                    <div
                                        onClick={() => this.addProductToCart(p)}
                                        key={p.id}
                                        className="item"
                                    >
                                        <img src={p.image_url} alt={p.name} />
                                        <h5>{p.name}</h5>
                                        <small className="text-muted d-block">
                                            Stock: {p.quantity}
                                        </small>
                                        <div className="text-center mt-2">
                                            <small className="text-success font-weight-bold d-block">
                                                Costo Proveedor USD: $
                                                {parseFloat(
                                                    p.purchase_price_usd ||
                                                        p.purchase_price ||
                                                        0
                                                ).toFixed(2)}
                                            </small>
                                            <small className="text-muted text-xs">
                                                {(
                                                    parseFloat(
                                                        p.purchase_price || 0
                                                    ) * window.dolarBcv
                                                )
                                                    .toFixed(2)
                                                    .replace(".", ",")
                                                    .replace(
                                                        /\B(?=(\d{3})+(?!\d))/g,
                                                        "."
                                                    )}{" "}
                                                Bs.
                                            </small>
                                            <small className="text-danger font-weight-bold d-block">
                                                Costo BCV: $
                                                {parseFloat(
                                                    window.calcularCostoRealBcv(
                                                        p.purchase_price_usd ||
                                                            p.purchase_price ||
                                                            0
                                                    )
                                                ).toFixed(2)}
                                                <span className="text-xs ml-1">
                                                    {window.diferenciaPorcentual(
                                                        p.purchase_price_usd ||
                                                            p.purchase_price ||
                                                            0
                                                    )}
                                                </span>
                                            </small>
                                            <small className="text-muted text-xs">
                                                {(
                                                    window.calcularCostoRealBcv(
                                                        p.purchase_price_usd ||
                                                            p.purchase_price ||
                                                            0
                                                    ) * window.dolarBcv
                                                )
                                                    .toFixed(2)
                                                    .replace(".", ",")
                                                    .replace(
                                                        /\B(?=(\d{3})+(?!\d))/g,
                                                        "."
                                                    )}{" "}
                                                Bs.
                                            </small>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* RIGHT SIDE - Purchase Cart */}
                <div className="col-lg-4 col-md-5">
                    {/* CONTENEDOR CON SCROLL FIJO */}
                    <div
                        style={{
                            maxHeight: "calc(100vh - 180px)",
                            overflowY: "auto",
                            paddingRight: "8px",
                        }}
                    >
                        {/* Información de Compra (Proveedor + Fecha) */}
                        <div className="card card-primary card-outline mb-3">
                            <div className="card-header">
                                <h3 className="card-title">
                                    <i className="fas fa-truck mr-2"></i>
                                    Información de Compra
                                </h3>
                            </div>
                            <div className="card-body">
                                <div className="form-group">
                                    <label>
                                        Proveedor{" "}
                                        <span className="text-danger">*</span>
                                    </label>
                                    <select
                                        className="form-control"
                                        value={supplier_id}
                                        onChange={this.setSupplierId}
                                    >
                                        <option value="">
                                            Seleccionar Proveedor
                                        </option>
                                        {suppliersList.map((sup) => (
                                            <option key={sup.id} value={sup.id}>
                                                {`${sup.first_name} ${sup.last_name}`}
                                            </option>
                                        ))}
                                    </select>
                                    <div className="form-group mt-3">
                                        <label className="font-weight-bold">
                                            Tasa Paralelo usada{" "}
                                            <span className="text-danger">
                                                *
                                            </span>
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            className="form-control form-control-lg text-center"
                                            placeholder="Ej: 365.50"
                                            value={
                                                this.state.parallel_rate_used
                                            }
                                            onChange={
                                                this.handleParallelRateChange
                                            }
                                            style={{
                                                fontSize: "1.0rem",
                                                fontWeight: "bold",
                                            }}
                                        />
                                        <small className="text-muted">
                                            Tasa exacta a la que te cobró este
                                            proveedor
                                        </small>
                                    </div>
                                </div>
                                <div className="form-group">
                                    <label>
                                        Fecha de Compra{" "}
                                        <span className="text-danger">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        className="form-control"
                                        value={purchase_date}
                                        onChange={this.handleDateChange}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Productos en el carrito */}
                        <div className="card card-secondary card-outline mb-3">
                            <div className="card-header">
                                <h3 className="card-title">
                                    <i className="fas fa-shopping-basket mr-2"></i>
                                    Productos
                                </h3>
                            </div>
                            <div className="card-body p-0 purchase-cart">
                                <table className="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th width="70">Cant.</th>
                                            <th width="90">Precio</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {cart.map((c) => (
                                            <tr key={c.id}>
                                                <td>
                                                    <small className="font-weight-bold">
                                                        {c.name}
                                                    </small>
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        className="form-control form-control-sm"
                                                        value={c.pivot.quantity}
                                                        onChange={(e) =>
                                                            this.handleChangeQty(
                                                                c.id,
                                                                e.target.value
                                                            )
                                                        }
                                                        min="1"
                                                    />
                                                </td>
                                                <td className="text-center">
                                                    <input
                                                        type="number"
                                                        className="form-control form-control-sm text-center mb-2"
                                                        value={
                                                            c.pivot
                                                                .purchase_price ||
                                                            0
                                                        }
                                                        onChange={(e) =>
                                                            this.handleChangePrice(
                                                                c.id,
                                                                e.target.value
                                                            )
                                                        }
                                                        min="0"
                                                        step="0.01"
                                                        style={{
                                                            fontWeight: "bold",
                                                        }}
                                                    />

                                                    {/* PRECIO PROVEEDOR */}
                                                    <div className="flex items-baseline justify-center text-sm font-bold text-success leading-none">
                                                        <span>$</span>
                                                        <span className="mx-0.5">
                                                            {parseFloat(
                                                                c.pivot
                                                                    .purchase_price ||
                                                                    0
                                                            ).toFixed(2)}
                                                        </span>
                                                        <span className="text-gray-500 text-xs font-normal ml-1">
                                                            (Proveedor)
                                                        </span>
                                                    </div>

                                                    {/* BOLÍVARES PROVEEDOR */}
                                                    <div className="text-xs mt-1 leading-none text-muted">
                                                        {(
                                                            (c.pivot
                                                                .purchase_price ||
                                                                0) *
                                                            window.dolarBcv
                                                        )
                                                            .toFixed(2)
                                                            .replace(".", ",")
                                                            .replace(
                                                                /\B(?=(\d{3})+(?!\d))/g,
                                                                "."
                                                            )}{" "}
                                                        Bs.
                                                    </div>

                                                    {/* COSTO REAL BCV – AHORA USA TU TASA */}
                                                    <div className="flex items-baseline justify-center text-sm font-bold text-danger leading-none mt-1">
                                                        <span>$</span>
                                                        <span className="mx-0.5">
                                                            {this.calcularCostoRealBcv(
                                                                c.pivot
                                                                    .purchase_price ||
                                                                    0
                                                            )}
                                                        </span>
                                                        <span className="text-gray-500 text-xs font-normal ml-1">
                                                            {this.diferenciaPorcentual(
                                                                c.pivot
                                                                    .purchase_price ||
                                                                    0
                                                            )}{" "}
                                                            (Costo BCV)
                                                        </span>
                                                    </div>

                                                    {/* BOLÍVARES DEL COSTO REAL */}
                                                    <div className="text-center text-xs text-muted leading-none mt-1 font-medium">
                                                        {(
                                                            this.calcularCostoRealBcv(
                                                                c.pivot
                                                                    .purchase_price ||
                                                                    0
                                                            ) * window.dolarBcv
                                                        )
                                                            .toFixed(2)
                                                            .replace(".", ",")
                                                            .replace(
                                                                /\B(?=(\d{3})+(?!\d))/g,
                                                                "."
                                                            )}{" "}
                                                        Bs.
                                                    </div>
                                                </td>
                                                <td>
                                                    <button
                                                        className="btn btn-danger btn-xs"
                                                        onClick={() =>
                                                            this.handleClickDelete(
                                                                c.id
                                                            )
                                                        }
                                                    >
                                                        <i className="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                {cart.length === 0 && (
                                    <div className="text-center text-muted py-4">
                                        <p>No hay productos en el carrito</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Notas */}
                        {cart.length > 0 && (
                            <div className="card mb-3">
                                <div className="card-body py-3">
                                    <label className="small">
                                        Notas (opcional)
                                    </label>
                                    <textarea
                                        className="form-control form-control-sm"
                                        rows="2"
                                        value={notes}
                                        onChange={this.handleNotesChange}
                                        placeholder="Agregar notas..."
                                    />
                                </div>
                            </div>
                        )}

                        {/* Estado */}
                        {cart.length > 0 && (
                            <div className="card mb-3">
                                <div className="card-body py-3">
                                    <label className="small font-weight-bold mb-2">
                                        <i className="fas fa-flag mr-1"></i>
                                        Estado
                                    </label>
                                    <div
                                        className="btn-group btn-group-toggle w-100"
                                        data-toggle="buttons"
                                    >
                                        <label
                                            className={`btn btn-sm ${
                                                status === "pending"
                                                    ? "btn-warning active"
                                                    : "btn-outline-warning"
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                value="pending"
                                                checked={status === "pending"}
                                                onChange={
                                                    this.handleStatusChange
                                                }
                                            />
                                            Pendiente
                                        </label>
                                        <label
                                            className={`btn btn-sm ${
                                                status === "completed"
                                                    ? "btn-success active"
                                                    : "btn-outline-success"
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                value="completed"
                                                checked={status === "completed"}
                                                onChange={
                                                    this.handleStatusChange
                                                }
                                            />
                                            Completado
                                        </label>
                                        <label
                                            className={`btn btn-sm ${
                                                status === "cancelled"
                                                    ? "btn-danger active"
                                                    : "btn-outline-danger"
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                value="cancelled"
                                                checked={status === "cancelled"}
                                                onChange={
                                                    this.handleStatusChange
                                                }
                                            />
                                            Cancelado
                                        </label>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* MONTO TOTAL – LA VERDAD ABSOLUTA, CON TU TASA */}
                        {cart.length > 0 && (
                            <div className="card bg-light border-0 shadow-sm mb-3">
                                <div className="card-body text-center py-6">
                                    <small className="text-muted d-block mb-4 font-medium">
                                        Monto Total de la Compra
                                    </small>

                                    {/* PRECIO PROVEEDOR – Lo que pagas (en dólares) */}
                                    <div className="mb-5">
                                        <div className="text-success font-weight-bold leading-none">
                                            $ {this.getTotal(cart)}
                                        </div>
                                        <div className="text-gray-600 font-weight-bold mt-3 font-bold">
                                            {(
                                                this.getTotal(cart) *
                                                window.dolarBcv
                                            )
                                                .toFixed(2)
                                                .replace(".", ",")
                                                .replace(
                                                    /\B(?=(\d{3})+(?!\d))/g,
                                                    "."
                                                )}{" "}
                                            Bs.
                                        </div>
                                        <div className="text-muted text-sm mt-5">
                                            Pago al proveedor (tasa paralela que
                                            ingresaste)
                                        </div>
                                    </div>

                                    {/* COSTO REAL BCV – LA VERDAD QUE ENTRA A TU INVENTARIO */}
                                    <div>
                                        <div className="text-danger font-weight-bold leading-none">
                                            ${" "}
                                            {parseFloat(
                                                cart.reduce((acc, c) => {
                                                    const costoReal =
                                                        this.calcularCostoRealBcv(
                                                            c.pivot
                                                                .purchase_price ||
                                                                0
                                                        );
                                                    return (
                                                        acc +
                                                        costoReal *
                                                            c.pivot.quantity
                                                    );
                                                }, 0)
                                            ).toFixed(2)}
                                        </div>

                                        <div className="text-gray-600 font-weight-bold">
                                            {parseFloat(
                                                cart.reduce((acc, c) => {
                                                    const costoReal =
                                                        this.calcularCostoRealBcv(
                                                            c.pivot
                                                                .purchase_price ||
                                                                0
                                                        );
                                                    return (
                                                        acc +
                                                        costoReal *
                                                            c.pivot.quantity
                                                    );
                                                }, 0) * window.dolarBcv
                                            )
                                                .toFixed(2)
                                                .replace(".", ",")
                                                .replace(
                                                    /\B(?=(\d{3})+(?!\d))/g,
                                                    "."
                                                )}{" "}
                                            Bs.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Botones siempre visibles */}
                        {cart.length > 0 && (
                            <div className="purchase-actions">
                                <div className="row">
                                    <div className="col-6">
                                        <button
                                            type="button"
                                            className="btn btn-danger btn-block"
                                            onClick={this.handleEmptyCart}
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                    <div className="col-6">
                                        <button
                                            type="button"
                                            className="btn btn-primary btn-block"
                                            disabled={!supplier_id}
                                            onClick={this.handleClickSubmit}
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }
}

export default Purchase;

// Render component
const root = document.getElementById("purchase");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Purchase />);
}
