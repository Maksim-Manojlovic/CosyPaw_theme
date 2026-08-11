/**
 * CosyPaw dev aggregate entry (Vite dev server only).
 *
 * Production splits assets per context (see inc/Assets.php); during `npm run dev`
 * we load everything through one module so HMR covers all styles + components.
 */
import './app.js';
import '../css/content.css';
import '../css/woocommerce.css';
import './landing.js';
