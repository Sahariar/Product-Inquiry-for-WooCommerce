(function (wp) {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, ToggleControl, Placeholder, Notice } = wp.components;
	const { useState, useEffect, createElement: el } = wp.element;
	const { apiFetch } = wp;

	registerBlockType("product-inquiry/inquiry-form", {
		edit: function Edit(props) {
			const { attributes, setAttributes } = props;
			const { productId, showTitle } = attributes;
			const blockProps = useBlockProps();

			const [productTitle, setProductTitle] = useState("");
			const [isLoading, setIsLoading] = useState(false);
			const [error, setError] = useState("");

			useEffect(() => {
				if (!productId) {
					setProductTitle("");
					return;
				}

				setIsLoading(true);
				setError("");

				wp.apiFetch({
					path: `/wc/v3/products/${productId}`,
				})
					.then(function (product) {
						setProductTitle(product.name);
						setIsLoading(false);
					})
					.catch(function () {
						setError(__("Product not found. Please enter a valid product ID.", "product-inquiry-for-woocommerce"));
						setProductTitle("");
						setIsLoading(false);
					});
			}, [productId]);

			return el(
				"div",
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: __("Product Settings", "product-inquiry-for-woocommerce"),
							initialOpen: true,
						},
						el(TextControl, {
							label: __("Product ID", "product-inquiry-for-woocommerce"),
							help: __("Enter the WooCommerce product ID to display the inquiry form for.", "product-inquiry-for-woocommerce"),
							value: productId || "",
							onChange: function (value) {
								setAttributes({ productId: parseInt(value) || 0 });
							},
							type: "number",
							min: "1",
						}),
						el(ToggleControl, {
							label: __("Show Product Title", "product-inquiry-for-woocommerce"),
							help: __("Display the product name above the form.", "product-inquiry-for-woocommerce"),
							checked: showTitle,
							onChange: function (value) {
								setAttributes({ showTitle: value });
							},
						})
					)
				),
				!productId
					? el(
							Placeholder,
							{
								icon: "email-alt",
								label: __("Product Inquiry Form", "product-inquiry-for-woocommerce"),
								instructions: __("Select a product ID in the block settings to display the inquiry form.", "product-inquiry-for-woocommerce"),
							},
							el(TextControl, {
								label: __("Product ID", "product-inquiry-for-woocommerce"),
								value: productId || "",
								onChange: function (value) {
									setAttributes({ productId: parseInt(value) || 0 });
								},
								type: "number",
								min: "1",
								placeholder: __("Enter product ID", "product-inquiry-for-woocommerce"),
							})
					  )
					: el(
							"div",
							{ className: "pi-block-preview" },
							error &&
								el(
									Notice,
									{
										status: "error",
										isDismissible: false,
									},
									error
								),
							isLoading && el("div", { className: "pi-block-loading" }, __("Loading product...", "product-inquiry-for-woocommerce")),
							!isLoading &&
								productTitle &&
								el(
									"div",
									null,
									showTitle && el("div", { className: "pi-block-title" }, el("h3", null, __("Inquire About:", "product-inquiry-for-woocommerce") + " " + productTitle)),
									el(
										"div",
										{ className: "pi-block-form-preview" },
										el("div", { className: "pi-form-field" }, el("label", null, __("Your Name", "product-inquiry-for-woocommerce") + " *"), el("input", { type: "text", placeholder: __("Enter your name", "product-inquiry-for-woocommerce"), disabled: true })),
										el("div", { className: "pi-form-field" }, el("label", null, __("Your Email", "product-inquiry-for-woocommerce") + " *"), el("input", { type: "email", placeholder: __("Enter your email", "product-inquiry-for-woocommerce"), disabled: true })),
										el("div", { className: "pi-form-field" }, el("label", null, __("Phone Number", "product-inquiry-for-woocommerce") + " (" + __("Optional", "product-inquiry-for-woocommerce") + ")"), el("input", { type: "tel", placeholder: __("Enter your phone number", "product-inquiry-for-woocommerce"), disabled: true })),
										el("div", { className: "pi-form-field" }, el("label", null, __("Your Message", "product-inquiry-for-woocommerce") + " *"), el("textarea", { rows: "5", placeholder: __("Enter your inquiry message", "product-inquiry-for-woocommerce"), disabled: true })),
										el("button", { className: "button", disabled: true }, __("Send Inquiry", "product-inquiry-for-woocommerce")),
										el(
											Notice,
											{
												status: "info",
												isDismissible: false,
											},
											__("This is a preview. The form will be functional on the front-end.", "product-inquiry-for-woocommerce")
										)
									)
								)
					  )
			);
		},
		save: function () {
			return null;
		},
	});
})(window.wp);
