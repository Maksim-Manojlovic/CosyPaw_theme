import { defineConfig } from 'vite';
import { writeFileSync, rmSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';

const HOT_FILE = resolve(__dirname, 'hot');
const DEV_ORIGIN = 'http://localhost:5173';

/**
 * Writes a `hot` file containing the dev server origin while `vite` runs, and
 * removes it on shutdown. Assets.php (inc/Assets.php) checks for this file to
 * decide between the dev (HMR) and production strategies — same convention used
 * by Laravel/Vite plugins.
 */
function hotFilePlugin() {
	const cleanup = () => {
		if (existsSync(HOT_FILE)) rmSync(HOT_FILE);
	};

	return {
		name: 'cosypaw-hot-file',
		apply: 'serve',
		configureServer() {
			writeFileSync(HOT_FILE, DEV_ORIGIN);
			process.on('exit', cleanup);
			process.on('SIGINT', () => { cleanup(); process.exit(); });
			process.on('SIGTERM', () => { cleanup(); process.exit(); });
		},
		closeBundle: cleanup,
	};
}

export default defineConfig({
	plugins: [hotFilePlugin()],
	base: '/wp-content/themes/cosypaw/dist/',
	server: {
		host: 'localhost',
		port: 5173,
		strictPort: true,
		cors: true,
	},
	build: {
		outDir: 'dist',
		emptyOutDir: true,
		// Produces dist/.vite/manifest.json (read by inc/Assets.php in production).
		manifest: true,
		rollupOptions: {
			// Per-context entries — Assets.php enqueues only what each view needs.
			input: {
				app: resolve(__dirname, 'assets/css/main.css'),
				landing: resolve(__dirname, 'assets/js/landing.js'),
				content: resolve(__dirname, 'assets/css/content.css'),
				woocommerce: resolve(__dirname, 'assets/css/woocommerce.css'),
			},
		},
	},
});
