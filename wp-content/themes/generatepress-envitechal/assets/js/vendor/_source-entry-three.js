/* Slim Three.js entry for the Envi Tech AL hero.
 * Re-exports only the symbols eta-hero-twin.js actually references, so
 * Rollup can tree-shake the rest of the library away. Keep this list in
 * sync with the THREE.* usages in the hero module. */
export {
  WebGLRenderer,
  Scene,
  Color,
  Fog,
  PerspectiveCamera,
  Group,
  Vector3,

  HemisphereLight,
  DirectionalLight,
  PointLight,

  BufferGeometry,
  BufferAttribute,
  Float32BufferAttribute,
  BoxGeometry,
  CylinderGeometry,
  CircleGeometry,
  EdgesGeometry,

  Mesh,
  Line,
  LineSegments,
  Points,
  Sprite,

  MeshLambertMaterial,
  LineBasicMaterial,
  SpriteMaterial,
  ShaderMaterial,

  CanvasTexture,
  AdditiveBlending,
  NormalBlending
} from 'three';
