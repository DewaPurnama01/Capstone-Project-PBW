import { NavLink, useNavigate } from 'react-router-dom';
import { Lock, Coffee, LogOut } from 'lucide-react';
import { NAV_ITEMS } from '../nav';
import { useAuth } from '../context/AuthContext';

export default function Sidebar() {
  const { user, hasAccess, logout } = useAuth();
  const navigate = useNavigate();

  if (!user) return null;

  function handleLogout() {
    logout();
    navigate('/login');
  }

  return (
    <aside className="w-64 shrink-0 bg-cafe-green-800 text-cream-100 flex flex-col h-screen sticky top-0">
      <div className="px-5 py-6 border-b border-white/10">
        <div className="flex items-center gap-2">
          <Coffee size={22} className="text-cream-100" />
          <div>
            <p className="font-semibold leading-tight">Cafe CNS</p>
            <p className="text-xs text-cream-100/60 leading-tight">Sistem Informasi Manajemen</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
        {NAV_ITEMS.map((item) => {
          const allowed = hasAccess(item.roles);
          const Icon = item.icon;

          if (!allowed) {
            return (
              <div
                key={item.to}
                title="Modul ini di luar hak akses role kamu"
                className="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg text-cream-100/35 cursor-not-allowed select-none"
              >
                <span className="flex items-center gap-3 text-sm">
                  <Icon size={18} />
                  {item.label}
                </span>
                <Lock size={14} />
              </div>
            );
          }

          return (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors ${
                  isActive
                    ? 'bg-cafe-green-600 text-white font-medium'
                    : 'text-cream-100/85 hover:bg-white/10'
                }`
              }
            >
              <Icon size={18} />
              {item.label}
            </NavLink>
          );
        })}
      </nav>

      <div className="px-3 py-4 border-t border-white/10">
        <div className="flex items-center gap-3 px-2">
          <div className="w-9 h-9 rounded-full bg-cafe-green-600 flex items-center justify-center text-sm font-semibold">
            {user.avatar_initial}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium truncate">{user.name}</p>
            <p className="text-xs text-cream-100/60 capitalize">{user.role}</p>
          </div>
          <button onClick={handleLogout} title="Logout" className="p-1.5 rounded hover:bg-white/10">
            <LogOut size={16} />
          </button>
        </div>
      </div>
    </aside>
  );
}
