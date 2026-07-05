import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import api from '../api/client';
import type { User } from '../types';

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => void;
  hasAccess: (roles: string[]) => boolean;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const stored = localStorage.getItem('cns_user');
    const token = localStorage.getItem('cns_token');
    if (stored && token) {
      setUser(JSON.parse(stored));
    }
    setLoading(false);
  }, []);

  async function login(username: string, password: string) {
    const res = await api.post('/auth/login', { username, password });
    const { token, user } = res.data;
    localStorage.setItem('cns_token', token);
    localStorage.setItem('cns_user', JSON.stringify(user));
    setUser(user);
  }

  function logout() {
    api.post('/auth/logout').catch(() => {});
    localStorage.removeItem('cns_token');
    localStorage.removeItem('cns_user');
    setUser(null);
  }

  function hasAccess(roles: string[]) {
    if (!user) return false;
    return roles.includes(user.role);
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, hasAccess }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
