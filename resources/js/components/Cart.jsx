import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            products: [],
            customers: [],
            barcode: "",
            search: "",
            customer_id: "",
            translations: {},
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCustomers();
        this.loadProducts();
        this.loadCart();
    }

    // load the transaltions for the react component
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

    loadCustomers() {
        axios
            .get(`/admin/customers`, {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            })
            .then((res) => {
                const customers = res.data;
                this.setState({ customers });
            })
            .catch((error) => {
                console.error("Error loading customers:", error);
                this.setState({ customers: [] });
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

    handleOnChangeBarcode(event) {
        const barcode = event.target.value;
        this.setState({ barcode });
    }

    loadCart() {
        axios
            .get("/admin/cart", {
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
    handleScanBarcode(event) {
        event.preventDefault();
        const { barcode } = this.state;
        if (!!barcode) {
            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    this.loadCart();
                    this.setState({ barcode: "" });
                })
                .catch((err) => {
                    Swal.fire(
                        "Error!",
                        "Codigo de barras de producto incorrecto!",
                        "error"
                    );
                });
        }
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
            .post("/admin/cart/change-qty", { product_id, quantity: qty })
            .then((res) => {})
            .catch((err) => {
                Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total).toFixed(2);
    }
    handleClickDelete(product_id) {
        axios
            .post("/admin/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }
    handleEmptyCart() {
        axios.post("/admin/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }
    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }
    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(barcode) {
        let product = this.state.products.find((p) => p.barcode === barcode);
        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update quantity
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (
                            c.id === product.id &&
                            product.quantity > c.pivot.quantity
                        ) {
                            c.pivot.quantity = c.pivot.quantity + 1;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.quantity > 0) {
                    product = {
                        ...product,
                        pivot: {
                            quantity: 1,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/admin/cart", { barcode })
                .then((res) => {
                    // this.loadCart();
                })
                .catch((err) => {
                    Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }
    handleClickSubmit() {
        const cart = this.state.cart;

        // Total usando precio normal (dólares)
        const totalNormal = sum(
            cart.map((c) => c.pivot.quantity * parseFloat(c.price))
        );

        // Total usando precio alternativo cuando pagan en Bs
        const totalConPrecioBs = sum(
            cart.map((c) => {
                const precioUsado = c.price_bsd
                    ? parseFloat(c.price_bsd)
                    : parseFloat(c.price);
                return c.pivot.quantity * precioUsado;
            })
        );

        Swal.fire({
            title: "Confirmar Venta",
            html: `
            <div class="text-left mb-4">
                <p><strong>Precio en dólares:</strong> $ ${totalNormal.toFixed(
                    2
                )}</p>
                ${
                    totalConPrecioBs !== totalNormal
                        ? `
                <p><strong>Pago en bolívares (precio alternativo):</strong> 
                   $ ${totalConPrecioBs.toFixed(2)} 
                   <br>
                <p><strong>Monto en Bolivares:</strong>
                   <span>${(
                       totalConPrecioBs * window.dolarBcv
                   )
                       .toFixed(2)
                       .replace(".", ",")} Bs.</span>
                </p>`
                        : ""
                }
            </div>
            <hr>
            <div class="text-center">
                <label class="font-weight-bold h5">Monto a cobrar</label>
                <input type="number" id="amount-usd" class="form-control form-control-lg text-center" 
                       value="${totalConPrecioBs.toFixed(
                           2
                       )}" step="0.01" min="${totalConPrecioBs.toFixed(2)}" 
                       style="font-size: 2em;">
                <div class="mt-3">
                    <small class="text-muted">En bolívares:</small>
                    <h3 id="equiv-bs" class="text-primary">
                        ${(totalConPrecioBs * window.dolarBcv)
                            .toFixed(2)
                            .replace(".", ",")
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ".")} Bs.
                    </h3>
                </div>
            </div>
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="bs-payment" checked>
                <label class="form-check-label text-danger font-weight-bold">
                    Pago en bolívares ${
                        totalConPrecioBs !== totalNormal
                            ? "(precio alternativo aplicado)"
                            : ""
                    }
                </label>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: "Confirmar Venta",
            preConfirm: () => {
                const amount = parseFloat(
                    document.getElementById("amount-usd").value
                );
                const isBs = document.getElementById("bs-payment").checked;
                const minimo = isBs ? totalConPrecioBs : totalNormal;

                if (amount < minimo) {
                    Swal.showValidationMessage(
                        `El monto no puede ser menor a $ ${minimo.toFixed(2)}`
                    );
                    return false;
                }
                return { amount, isBs };
            },
            didOpen: () => {
                document
                    .getElementById("amount-usd")
                    .addEventListener("input", function () {
                        const usd = parseFloat(this.value) || 0;
                        const bs = usd * window.dolarBcv;
                        document.getElementById("equiv-bs").textContent =
                            bs
                                .toFixed(2)
                                .replace(".", ",")
                                .replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " Bs.";
                    });
            },
        }).then((result) => {
            if (result.isConfirmed) {
                const { amount, isBs } = result.value;
                axios
                    .post("/admin/orders", {
                        customer_id: this.state.customer_id || null,
                        amount: amount,
                        is_bs_payment: isBs,
                        notes: isBs
                            ? `Pago en Bs: ${(amount * window.dolarBcv)
                                  .toFixed(2)
                                  .replace(".", ",")} Bs.`
                            : null,
                    })
                    .then(() => {
                        this.loadCart();
                        Swal.fire("Venta exitosa!", "", "success");
                    });
            }
        });
    }
    render() {
        const {
            cart = [],
            products = [],
            customers = [],
            barcode = "",
            translations = {},
        } = this.state;
        return (
            <div className="row">
                <div className="col-md-6 col-lg-4">
                    <div className="row mb-2">
                        <div className="col">
                            <form onSubmit={this.handleScanBarcode}>
                                <input
                                    type="text"
                                    className="form-control"
                                    placeholder={
                                        translations[
                                            "buscar_codigo_de_barras"
                                        ] || "Codigo de Barras"
                                    }
                                    value={barcode}
                                    onChange={this.handleOnChangeBarcode}
                                />
                            </form>
                        </div>
                        <div className="col">
                            <select
                                className="form-control"
                                onChange={this.setCustomerId}
                            >
                                <option value="">
                                    {translations["cliente_anonimo"] ||
                                        "Cliente Anonimo"}
                                </option>
                                {customers.map((cus) => (
                                    <option
                                        key={cus.id}
                                        value={cus.id}
                                    >{`${cus.first_name} ${cus.last_name}`}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="user-cart">
                        <div className="card">
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            {translations["Producto"] ||
                                                "Producto"}
                                        </th>
                                        <th>
                                            {translations["Cantidad"] ||
                                                "Cantidad"}
                                        </th>
                                        <th className="text-right">
                                            {translations["Precio"] || "Precio"}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty"
                                                    value={c.pivot.quantity}
                                                    onChange={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }
                                                />
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() =>
                                                        this.handleClickDelete(
                                                            c.id
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                <div>
                                                    <strong className="text-success">
                                                        ${" "}
                                                        {(
                                                            c.price *
                                                            c.pivot.quantity
                                                        ).toFixed(2)}
                                                    </strong>
                                                </div>
                                                <small className="text-muted">
                                                    {(
                                                        c.price *
                                                        c.pivot.quantity *
                                                        window.dolarBcv
                                                    )
                                                        .toFixed(2)
                                                        .replace(".", ",")
                                                        .replace(
                                                            /\B(?=(\d{3})+(?!\d))/g,
                                                            "."
                                                        )}{" "}
                                                    Bs.
                                                </small>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="row mb-3">
                        <div className="col">
                            <strong>{translations["total"] || "Total"}:</strong>
                        </div>
                        <div className="col text-right">
                            <div className="text-success font-weight-bold">
                                $ {this.getTotal(cart)}
                            </div>
                            <small className="text-muted">
                                {(this.getTotal(cart) * window.dolarBcv)
                                    .toFixed(2)
                                    .replace(".", ",")
                                    .replace(/\B(?=(\d{3})+(?!\d))/g, ".")}{" "}
                                Bs.
                            </small>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                {translations["cancelar"] || "Cancelar"}
                            </button>
                        </div>
                        <div className="col">
                            <button
                                type="button"
                                className="btn btn-primary btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                {translations["Confimar_Venta"] ||
                                    "Confirmar Venta"}
                            </button>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 col-lg-8">
                    <div className="mb-2">
                        <input
                            type="text"
                            className="form-control"
                            placeholder={
                                (translations["buscar_producto"] ||
                                    "Buscar Producto") + "..."
                            }
                            onChange={this.handleChangeSearch}
                            onKeyDown={this.handleSeach}
                        />
                    </div>
                    <div className="order-product">
                        {products.map((p) => (
                            <div
                                onClick={() => this.addProductToCart(p.barcode)}
                                key={p.id}
                                className="item"
                            >
                                <img src={p.image_url} alt="" />
                                <h5
                                    style={
                                        window.APP.warning_quantity > p.quantity
                                            ? { color: "red" }
                                            : {}
                                    }
                                >
                                    {p.name}({p.quantity})
                                </h5>
                                <div className="text-center mt-2">
                                    {/* Precio normal – Pago en dólares */}
                                    <div className="text-primary/80 text-xs">
                                        Cobro en Dolares:
                                    </div>
                                    <div className="text-success font-weight-bold text-sm">
                                        $ {parseFloat(p.price).toFixed(2)}
                                    </div>

                                    {/* Precio alternativo – Pago en bolívares */}
                                    {p.price_bsd &&
                                        parseFloat(p.price_bsd) !==
                                            parseFloat(p.price) && (
                                            <div className="text-center">
                                                <div className="text-primary/80 text-xs">
                                                    Cobro en Bs:
                                                </div>
                                                <div className="text-primary font-weight-bold text-sm leading-none">
                                                    ${" "}
                                                    {parseFloat(
                                                        p.price_bsd
                                                    ).toFixed(2)}
                                                </div>
                                            </div>
                                        )}

                                    {/* Precio en bolívares con tasa BCV actual */}
                                    <div className="text-muted text-xs mt-2">
                                        {window.dolarBcv
                                            ? (
                                                  (p.price_bsd || p.price) *
                                                  window.dolarBcv
                                              )
                                                  .toFixed(2)
                                                  .replace(".", ",")
                                                  .replace(
                                                      /\B(?=(\d{3})+(?!\d))/g,
                                                      "."
                                                  )
                                            : "..."}{" "}
                                        Bs.
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
